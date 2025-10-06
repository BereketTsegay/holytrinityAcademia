<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookBorrowing;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $query = Book::query();

        if ($request->has('search')) {
            $query->search($request->search);
        }

        if ($request->has('category')) {
            $query->byCategory($request->category);
        }

        if ($request->has('available')) {
            $query->available();
        }

        $books = $query->orderBy('title')
                      ->paginate($request->per_page ?? 20);

        return response()->json($books);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'required|string|unique:books,isbn',
            'quantity' => 'required|integer|min:1',
            'publisher' => 'nullable|string|max:255',
            'published_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'category' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'edition' => 'nullable|string|max:255'
        ]);

        $book = Book::create([
            'title' => $request->title,
            'author' => $request->author,
            'isbn' => $request->isbn,
            'quantity' => $request->quantity,
            'available' => $request->quantity, // Initially all books are available
            'publisher' => $request->publisher,
            'published_year' => $request->published_year,
            'category' => $request->category,
            'description' => $request->description,
            'location' => $request->location,
            'edition' => $request->edition
        ]);

        return response()->json([
            'message' => 'Book added successfully',
            'book' => $book
        ], 201);
    }

    public function show(Book $book)
    {
        $book->load(['borrowings.student', 'currentBorrowings.student']);
        return response()->json($book);
    }

    public function update(Request $request, Book $book)
    {
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'author' => 'sometimes|required|string|max:255',
            'isbn' => 'sometimes|required|string|unique:books,isbn,' . $book->id,
            'quantity' => 'sometimes|required|integer|min:1',
            'publisher' => 'nullable|string|max:255',
            'published_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'category' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'edition' => 'nullable|string|max:255'
        ]);

        // Update available count if quantity changes
        if ($request->has('quantity')) {
            $diff = $request->quantity - $book->quantity;
            $request->merge(['available' => max(0, $book->available + $diff)]);
        }

        $book->update($request->all());

        return response()->json([
            'message' => 'Book updated successfully',
            'book' => $book
        ]);
    }

    public function destroy(Book $book)
    {
        // Check if book has active borrowings
        if ($book->currentBorrowings()->exists()) {
            return response()->json([
                'message' => 'Cannot delete book with active borrowings'
            ], 422);
        }

        $book->delete();

        return response()->json([
            'message' => 'Book deleted successfully'
        ]);
    }

    public function borrow(Request $request, Book $book)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'due_date' => 'required|date|after:today'
        ]);

        // Check if book is available
        if (!$book->canBeBorrowed()) {
            return response()->json([
                'message' => 'This book is not available for borrowing'
            ], 422);
        }

        // Check if student has already borrowed this book
        $existingBorrowing = BookBorrowing::where('book_id', $book->id)
            ->where('student_id', $request->student_id)
            ->where('status', 'borrowed')
            ->first();

        if ($existingBorrowing) {
            return response()->json([
                'message' => 'Student has already borrowed this book'
            ], 422);
        }

        // Create borrowing record
        $borrowing = BookBorrowing::create([
            'book_id' => $book->id,
            'student_id' => $request->student_id,
            'borrowed_date' => now(),
            'due_date' => $request->due_date,
            'status' => 'borrowed'
        ]);

        // Update book availability
        $book->markAsBorrowed();

        return response()->json([
            'message' => 'Book borrowed successfully',
            'borrowing' => $borrowing->load('student')
        ], 201);
    }

    public function returnBook(Book $book, Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id'
        ]);

        // Find active borrowing
        $borrowing = BookBorrowing::where('book_id', $book->id)
            ->where('student_id', $request->student_id)
            ->where('status', 'borrowed')
            ->first();

        if (!$borrowing) {
            return response()->json([
                'message' => 'No active borrowing found for this book and student'
            ], 404);
        }

        // Update borrowing record
        $borrowing->update([
            'returned_date' => now(),
            'status' => 'returned'
        ]);

        // Update book availability
        $book->markAsReturned();

        return response()->json([
            'message' => 'Book returned successfully',
            'borrowing' => $borrowing
        ]);
    }

    public function categories()
    {
        $categories = Book::distinct('category')
                         ->whereNotNull('category')
                         ->pluck('category')
                         ->sort()
                         ->values();

        return response()->json($categories);
    }
    public function reports(Request $request)
{
    $request->validate([
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
        'report_type' => 'required|in:borrowings,overdue,popular,categories'
    ]);

    switch ($request->report_type) {
        case 'borrowings':
            return $this->borrowingsReport($request);
        case 'overdue':
            return $this->overdueReport($request);
        case 'popular':
            return $this->popularBooksReport($request);
        case 'categories':
            return $this->categoriesReport($request);
    }
}

private function borrowingsReport($request)
{
    $borrowings = BookBorrowing::with(['book', 'student'])
        ->whereBetween('borrowed_date', [$request->start_date, $request->end_date])
        ->selectRaw('
            COUNT(*) as total_borrowings,
            SUM(CASE WHEN status = "borrowed" THEN 1 ELSE 0 END) as active_borrowings,
            SUM(CASE WHEN status = "returned" THEN 1 ELSE 0 END) as returned_books,
            SUM(CASE WHEN status = "overdue" THEN 1 ELSE 0 END) as overdue_books,
            DATE(borrowed_date) as date
        ')
        ->groupBy('date')
        ->orderBy('date')
        ->get();

    $summary = BookBorrowing::whereBetween('borrowed_date', [$request->start_date, $request->end_date])
        ->selectRaw('
            COUNT(*) as total_borrowings,
            SUM(CASE WHEN status = "borrowed" THEN 1 ELSE 0 END) as active_borrowings,
            SUM(CASE WHEN status = "returned" THEN 1 ELSE 0 END) as returned_books,
            SUM(CASE WHEN status = "overdue" THEN 1 ELSE 0 END) as overdue_books
        ')
        ->first();

    return response()->json([
        'borrowings' => $borrowings,
        'summary' => $summary,
        'period' => [
            'start_date' => $request->start_date,
            'end_date' => $request->end_date
        ]
    ]);
}

private function overdueReport($request)
{
    $overdueBooks = BookBorrowing::with(['book', 'student'])
        ->where('status', 'borrowed')
        ->where('due_date', '<', now())
        ->whereBetween('borrowed_date', [$request->start_date, $request->end_date])
        ->orderBy('due_date')
        ->get();

    $summary = [
        'total_overdue' => $overdueBooks->count(),
        'total_due_amount' => $overdueBooks->sum('fine_amount'),
        'average_overdue_days' => $overdueBooks->avg('overdue_days')
    ];

    return response()->json([
        'overdue_books' => $overdueBooks,
        'summary' => $summary,
        'generated_at' => now()
    ]);
}

private function popularBooksReport($request)
{
    $popularBooks = BookBorrowing::with('book')
        ->whereBetween('borrowed_date', [$request->start_date, $request->end_date])
        ->selectRaw('book_id, COUNT(*) as borrow_count')
        ->groupBy('book_id')
        ->orderBy('borrow_count', 'desc')
        ->limit(10)
        ->get()
        ->load('book');

    return response()->json([
        'popular_books' => $popularBooks,
        'period' => [
            'start_date' => $request->start_date,
            'end_date' => $request->end_date
        ]
    ]);
}

private function categoriesReport($request)
{
    $categories = Book::withCount(['borrowings' => function ($query) use ($request) {
            $query->whereBetween('borrowed_date', [$request->start_date, $request->end_date]);
        }])
        ->selectRaw('category, COUNT(*) as total_books, SUM(quantity) as total_copies, SUM(available) as available_copies')
        ->groupBy('category')
        ->orderBy('total_books', 'desc')
        ->get();

    return response()->json([
        'categories' => $categories,
        'period' => [
            'start_date' => $request->start_date,
            'end_date' => $request->end_date
        ]
    ]);
}
}
