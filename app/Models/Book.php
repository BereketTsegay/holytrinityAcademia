<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'author',
        'isbn',
        'quantity',
        'available',
        'publisher',
        'published_year',
        'category',
        'description',
        'location',
        'edition'
    ];

    protected $casts = [
        'published_year' => 'integer',
        'quantity' => 'integer',
        'available' => 'integer'
    ];

    // Relationships
    public function borrowings()
    {
        return $this->hasMany(BookBorrowing::class);
    }

    public function currentBorrowings()
    {
        return $this->hasMany(BookBorrowing::class)->where('status', 'borrowed');
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('available', '>', 0);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('author', 'like', "%{$search}%")
              ->orWhere('isbn', 'like', "%{$search}%");
        });
    }

    // Methods
    public function canBeBorrowed()
    {
        return $this->available > 0;
    }

    public function markAsBorrowed()
    {
        if ($this->canBeBorrowed()) {
            $this->decrement('available');
            return true;
        }
        return false;
    }

    public function markAsReturned()
    {
        $this->increment('available');
    }
}
