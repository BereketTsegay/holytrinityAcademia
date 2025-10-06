<template>
  <div class="container mx-auto px-4 py-8 pt-16">
    <div class="max-w-6xl mx-auto">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Profile Settings</h1>
        <p class="text-gray-600 mt-2">Manage your account information and preferences</p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Sidebar Navigation -->
        <div class="lg:col-span-1">
          <nav class="space-y-2 bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              @click="activeTab = tab.id"
              :class="[
                'w-full flex items-center px-3 py-3 text-sm font-medium rounded-md transition-all duration-200',
                activeTab === tab.id
                  ? 'bg-blue-50 text-blue-700 border border-blue-200 shadow-sm'
                  : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'
              ]"
            >
              <component :is="tab.icon" class="w-5 h-5 mr-3" />
              {{ tab.name }}
            </button>
          </nav>

          <!-- Quick Stats -->
          <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <h3 class="text-sm font-medium text-gray-900 mb-3">Quick Stats</h3>
            <div class="space-y-3">
              <div v-if="user.is_student" class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Classes</span>
                <span class="text-sm font-medium text-gray-900">{{ stats.classes_count || 0 }}</span>
              </div>
              <div v-if="user.is_student" class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Attendance</span>
                <span class="text-sm font-medium text-green-600">{{ stats.attendance_rate || '0%' }}</span>
              </div>
              <div v-if="user.is_teacher" class="flex justify-between items-center">
                <span class="text-sm text-gray-600">My Classes</span>
                <span class="text-sm font-medium text-gray-900">{{ stats.teacher_classes_count || 0 }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Borrowed Books</span>
                <span class="text-sm font-medium text-gray-900">{{ stats.borrowed_books_count || 0 }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Content Area -->
        <div class="lg:col-span-3">
          <!-- Profile Information Tab -->
          <div v-if="activeTab === 'profile'" class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Profile Information</h3>
              <p class="text-sm text-gray-600 mt-1">Update your account's profile information and photo</p>
            </div>
            <div class="p-6">
              <form @submit.prevent="updateProfile">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <!-- Photo Upload -->
                  <div class="md:col-span-2">
                    <div class="flex items-center space-x-6">
                      <div class="shrink-0 relative">
                        <img 
                          :src="form.photo_url || user.photo_url" 
                          class="h-24 w-24 object-cover rounded-full border-2 border-gray-300 shadow-sm"
                          alt="Profile photo"
                        >
                        <div class="absolute bottom-0 right-0">
                          <label for="photo-upload" class="cursor-pointer">
                            <div class="bg-blue-600 text-white p-1 rounded-full shadow-lg hover:bg-blue-700 transition-colors">
                              <CameraIcon class="w-4 h-4" />
                            </div>
                            <input 
                              id="photo-upload"
                              type="file" 
                              @change="handlePhotoChange"
                              class="hidden"
                              accept="image/*"
                            >
                          </label>
                        </div>
                      </div>
                      <div class="flex-1">
                        <p class="text-sm font-medium text-gray-700">Profile Photo</p>
                        <p class="text-xs text-gray-500 mt-1">JPG, PNG or GIF (Max 2MB)</p>
                        <p v-if="form.photo" class="text-xs text-green-600 mt-1">New photo selected</p>
                      </div>
                    </div>
                  </div>

                  <!-- Personal Information -->
                  <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700">
                      First Name *
                    </label>
                    <input
                      type="text"
                      id="first_name"
                      v-model="form.first_name"
                      required
                      class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                      :class="{ 'border-red-300': errors.first_name }"
                    >
                    <p v-if="errors.first_name" class="mt-1 text-sm text-red-600">
                      {{ errors.first_name[0] }}
                    </p>
                  </div>

                  <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700">
                      Last Name *
                    </label>
                    <input
                      type="text"
                      id="last_name"
                      v-model="form.last_name"
                      required
                      class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                      :class="{ 'border-red-300': errors.last_name }"
                    >
                    <p v-if="errors.last_name" class="mt-1 text-sm text-red-600">
                      {{ errors.last_name[0] }}
                    </p>
                  </div>

                  <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">
                      Email Address *
                    </label>
                    <input
                      type="email"
                      id="email"
                      v-model="form.email"
                      required
                      class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                      :class="{ 'border-red-300': errors.email }"
                    >
                    <p v-if="errors.email" class="mt-1 text-sm text-red-600">
                      {{ errors.email[0] }}
                    </p>
                  </div>

                  <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">
                      Phone Number
                    </label>
                    <input
                      type="tel"
                      id="phone"
                      v-model="form.phone"
                      class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                  </div>

                  <div>
                    <label for="date_of_birth" class="block text-sm font-medium text-gray-700">
                      Date of Birth
                    </label>
                    <input
                      type="date"
                      id="date_of_birth"
                      v-model="form.date_of_birth"
                      class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                  </div>

                  <div>
                    <label for="gender" class="block text-sm font-medium text-gray-700">
                      Gender
                    </label>
                    <select
                      id="gender"
                      v-model="form.gender"
                      class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                      <option value="">Select Gender</option>
                      <option value="male">Male</option>
                      <option value="female">Female</option>
                      <option value="other">Other</option>
                    </select>
                  </div>

                  <!-- Address Information -->
                  <div class="md:col-span-2">
                    <label for="address" class="block text-sm font-medium text-gray-700">
                      Address
                    </label>
                    <textarea
                      id="address"
                      v-model="form.address"
                      rows="3"
                      class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                      placeholder="Enter your complete address..."
                    ></textarea>
                  </div>

                  <!-- Student Specific Fields -->
                  <template v-if="user.is_student">
                    <div>
                      <label for="grade_level" class="block text-sm font-medium text-gray-700">
                        Grade Level
                      </label>
                      <input
                        type="text"
                        id="grade_level"
                        v-model="form.grade_level"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        placeholder="e.g., Grade 10"
                      >
                    </div>

                    <div>
                      <label for="section" class="block text-sm font-medium text-gray-700">
                        Section
                      </label>
                      <input
                        type="text"
                        id="section"
                        v-model="form.section"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        placeholder="e.g., A, B, C"
                      >
                    </div>
                  </template>

                  <!-- Teacher Specific Fields -->
                  <template v-if="user.is_teacher">
                    <div>
                      <label for="qualification" class="block text-sm font-medium text-gray-700">
                        Qualification
                      </label>
                      <input
                        type="text"
                        id="qualification"
                        v-model="form.qualification"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        placeholder="e.g., M.Ed, B.Sc"
                      >
                    </div>

                    <div>
                      <label for="specialization" class="block text-sm font-medium text-gray-700">
                        Specialization
                      </label>
                      <input
                        type="text"
                        id="specialization"
                        v-model="form.specialization"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        placeholder="e.g., Mathematics, Science"
                      >
                    </div>

                    <div class="md:col-span-2">
                      <label for="bio" class="block text-sm font-medium text-gray-700">
                        Bio
                      </label>
                      <textarea
                        id="bio"
                        v-model="form.bio"
                        rows="4"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Tell us about yourself..."
                      ></textarea>
                    </div>
                  </template>

                  <!-- Emergency Contact -->
                  <div class="md:col-span-2 border-t pt-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Emergency Contact</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                      <div>
                        <label for="emergency_contact_name" class="block text-sm font-medium text-gray-700">
                          Contact Name
                        </label>
                        <input
                          type="text"
                          id="emergency_contact_name"
                          v-model="form.emergency_contact_name"
                          class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                          placeholder="Full name"
                        >
                      </div>
                      <div>
                        <label for="emergency_contact_phone" class="block text-sm font-medium text-gray-700">
                          Contact Phone
                        </label>
                        <input
                          type="tel"
                          id="emergency_contact_phone"
                          v-model="form.emergency_contact_phone"
                          class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                          placeholder="Phone number"
                        >
                      </div>
                      <div class="md:col-span-2">
                        <label for="emergency_contact_relation" class="block text-sm font-medium text-gray-700">
                          Relationship
                        </label>
                        <input
                          type="text"
                          id="emergency_contact_relation"
                          v-model="form.emergency_contact_relation"
                          class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                          placeholder="e.g., Parent, Guardian, Spouse"
                        >
                      </div>
                    </div>
                  </div>

                  <!-- Medical Information -->
                  <div class="md:col-span-2 border-t pt-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Medical Information</h4>
                    <div class="space-y-4">
                      <div>
                        <label for="medical_information" class="block text-sm font-medium text-gray-700">
                          Medical Conditions
                        </label>
                        <textarea
                          id="medical_information"
                          v-model="form.medical_information"
                          rows="3"
                          class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                          placeholder="Any medical conditions or ongoing treatments..."
                        ></textarea>
                      </div>
                      <div>
                        <label for="allergies" class="block text-sm font-medium text-gray-700">
                          Allergies
                        </label>
                        <textarea
                          id="allergies"
                          v-model="form.allergies"
                          rows="2"
                          class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                          placeholder="List any allergies..."
                        ></textarea>
                      </div>
                      <div>
                        <label for="special_needs" class="block text-sm font-medium text-gray-700">
                          Special Needs
                        </label>
                        <textarea
                          id="special_needs"
                          v-model="form.special_needs"
                          rows="2"
                          class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                          placeholder="Any special requirements or accommodations..."
                        ></textarea>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="mt-8 flex justify-end space-x-3 pt-6 border-t">
                  <button
                    type="button"
                    @click="resetForm"
                    class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                  >
                    Reset Changes
                  </button>
                  <button
                    type="submit"
                    :disabled="loading"
                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                  >
                    <span v-if="loading">
                      <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                    </span>
                    Save Changes
                  </button>
                </div>
              </form>
            </div>
          </div>

          <!-- Password Tab -->
          <div v-if="activeTab === 'password'" class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Change Password</h3>
              <p class="text-sm text-gray-600 mt-1">Ensure your account is using a long, random password to stay secure</p>
            </div>
            <div class="p-6">
              <form @submit.prevent="updatePassword">
                <div class="space-y-6 max-w-md">
                  <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700">
                      Current Password *
                    </label>
                    <input
                      type="password"
                      id="current_password"
                      v-model="passwordForm.current_password"
                      required
                      class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                      :class="{ 'border-red-300': passwordErrors.current_password }"
                    >
                    <p v-if="passwordErrors.current_password" class="mt-1 text-sm text-red-600">
                      {{ passwordErrors.current_password[0] }}
                    </p>
                  </div>

                  <div>
                    <label for="new_password" class="block text-sm font-medium text-gray-700">
                      New Password *
                    </label>
                    <input
                      type="password"
                      id="new_password"
                      v-model="passwordForm.password"
                      required
                      class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                      :class="{ 'border-red-300': passwordErrors.password }"
                    >
                    <p v-if="passwordErrors.password" class="mt-1 text-sm text-red-600">
                      {{ passwordErrors.password[0] }}
                    </p>
                    <div class="mt-2 space-y-1">
                      <div class="flex items-center text-xs" :class="passwordStrength.length ? 'text-green-600' : 'text-gray-500'">
                        <CheckCircleIcon class="w-4 h-4 mr-1" />
                        At least 8 characters
                      </div>
                      <div class="flex items-center text-xs" :class="passwordStrength.mixedCase ? 'text-green-600' : 'text-gray-500'">
                        <CheckCircleIcon class="w-4 h-4 mr-1" />
                        Upper & lower case letters
                      </div>
                      <div class="flex items-center text-xs" :class="passwordStrength.numbers ? 'text-green-600' : 'text-gray-500'">
                        <CheckCircleIcon class="w-4 h-4 mr-1" />
                        Contains numbers
                      </div>
                    </div>
                  </div>

                  <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                      Confirm New Password *
                    </label>
                    <input
                      type="password"
                      id="password_confirmation"
                      v-model="passwordForm.password_confirmation"
                      required
                      class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                  </div>
                </div>

                <div class="mt-8 flex justify-end pt-6 border-t">
                  <button
                    type="submit"
                    :disabled="passwordLoading || !isPasswordValid"
                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                  >
                    <span v-if="passwordLoading">
                      <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                    </span>
                    Update Password
                  </button>
                </div>
              </form>
            </div>
          </div>

          <!-- Account Information Tab -->
          <div v-if="activeTab === 'account'" class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Account Information</h3>
              <p class="text-sm text-gray-600 mt-1">View your account details and system information</p>
            </div>
            <div class="p-6">
              <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <div>
                  <dt class="text-sm font-medium text-gray-500">Account Type</dt>
                  <dd class="mt-1 text-sm text-gray-900 capitalize">{{ user.role_name }}</dd>
                </div>
                
                <div v-if="user.student_id">
                  <dt class="text-sm font-medium text-gray-500">Student ID</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ user.student_id }}</dd>
                </div>

                <div v-if="user.employee_id">
                  <dt class="text-sm font-medium text-gray-500">Employee ID</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ user.employee_id }}</dd>
                </div>

                <div>
                  <dt class="text-sm font-medium text-gray-500">Account Status</dt>
                  <dd class="mt-1">
                    <span :class="[
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize',
                      user.status === 'active' ? 'bg-green-100 text-green-800' :
                      user.status === 'inactive' ? 'bg-yellow-100 text-yellow-800' :
                      'bg-red-100 text-red-800'
                    ]">
                      {{ user.status }}
                    </span>
                  </dd>
                </div>

                <div>
                  <dt class="text-sm font-medium text-gray-500">Email Verified</dt>
                  <dd class="mt-1">
                    <span v-if="user.email_verified_at" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                      Verified
                    </span>
                    <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                      Not Verified
                    </span>
                  </dd>
                </div>

                <div v-if="user.enrollment_date">
                  <dt class="text-sm font-medium text-gray-500">Enrollment Date</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDate(user.enrollment_date) }}</dd>
                </div>

                <div v-if="user.joining_date">
                  <dt class="text-sm font-medium text-gray-500">Joining Date</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDate(user.joining_date) }}</dd>
                </div>

                <div v-if="user.last_login_at">
                  <dt class="text-sm font-medium text-gray-500">Last Login</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDateTime(user.last_login_at) }}</dd>
                </div>

                <div>
                  <dt class="text-sm font-medium text-gray-500">Member Since</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDate(user.created_at) }}</dd>
                </div>
              </dl>

              <!-- Login History -->
              <div class="mt-8 border-t pt-6">
                <h4 class="text-lg font-medium text-gray-900 mb-4">Recent Login Activity</h4>
                <div v-if="loginHistory.length > 0" class="space-y-3">
                  <div
                    v-for="login in loginHistory"
                    :key="login.id"
                    class="flex items-center justify-between p-3 border border-gray-200 rounded-lg"
                  >
                    <div>
                      <p class="text-sm font-medium text-gray-900">{{ login.ip_address }}</p>
                      <p class="text-sm text-gray-500">{{ login.device_type }} • {{ login.browser }}</p>
                      <p class="text-xs text-gray-400">{{ formatDateTime(login.login_at) }}</p>
                    </div>
                    <div class="text-right">
                      <p class="text-sm text-gray-500">{{ login.country || 'Unknown' }}</p>
                      <p class="text-xs text-gray-400">{{ login.session_duration ? login.session_duration + ' min' : 'Active' }}</p>
                    </div>
                  </div>
                </div>
                <div v-else class="text-center py-4">
                  <p class="text-sm text-gray-500">No login history available</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Notifications Tab -->
          <div v-if="activeTab === 'notifications'" class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Notification Preferences</h3>
              <p class="text-sm text-gray-600 mt-1">Manage how you receive notifications and alerts</p>
            </div>
            <div class="p-6">
              <div class="space-y-6">
                <div class="flex items-center justify-between">
                  <div>
                    <h4 class="text-sm font-medium text-gray-900">Email Notifications</h4>
                    <p class="text-sm text-gray-500">Receive notifications via email</p>
                  </div>
                  <button
                    @click="toggleNotification('email')"
                    :class="[
                      'relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500',
                      user.email_notifications ? 'bg-blue-600' : 'bg-gray-200'
                    ]"
                  >
                    <span
                      :class="[
                        'pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200',
                        user.email_notifications ? 'translate-x-5' : 'translate-x-0'
                      ]"
                    />
                  </button>
                </div>

                <div class="flex items-center justify-between">
                  <div>
                    <h4 class="text-sm font-medium text-gray-900">SMS Notifications</h4>
                    <p class="text-sm text-gray-500">Receive notifications via SMS</p>
                  </div>
                  <button
                    @click="toggleNotification('sms')"
                    :class="[
                      'relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500',
                      user.sms_notifications ? 'bg-blue-600' : 'bg-gray-200'
                    ]"
                  >
                    <span
                      :class="[
                        'pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200',
                        user.sms_notifications ? 'translate-x-5' : 'translate-x-0'
                      ]"
                    />
                  </button>
                </div>

                <div class="flex items-center justify-between">
                  <div>
                    <h4 class="text-sm font-medium text-gray-900">Push Notifications</h4>
                    <p class="text-sm text-gray-500">Receive push notifications in the app</p>
                  </div>
                  <button
                    @click="toggleNotification('push')"
                    :class="[
                      'relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500',
                      user.push_notifications ? 'bg-blue-600' : 'bg-gray-200'
                    ]"
                  >
                    <span
                      :class="[
                        'pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200',
                        user.push_notifications ? 'translate-x-5' : 'translate-x-0'
                      ]"
                    />
                  </button>
                </div>
              </div>

              <div class="mt-8 pt-6 border-t">
                <h4 class="text-sm font-medium text-gray-900 mb-4">Notification Types</h4>
                <div class="space-y-4">
                  <div class="flex items-center">
                    <input
                      id="assignment_notifications"
                      type="checkbox"
                      checked
                      class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    >
                    <label for="assignment_notifications" class="ml-2 block text-sm text-gray-900">
                      New Assignments & Deadlines
                    </label>
                  </div>
                  <div class="flex items-center">
                    <input
                      id="attendance_notifications"
                      type="checkbox"
                      checked
                      class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    >
                    <label for="attendance_notifications" class="ml-2 block text-sm text-gray-900">
                      Attendance Updates
                    </label>
                  </div>
                  <div class="flex items-center">
                    <input
                      id="library_notifications"
                      type="checkbox"
                      checked
                      class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    >
                    <label for="library_notifications" class="ml-2 block text-sm text-gray-900">
                      Library Due Dates & Notices
                    </label>
                  </div>
                  <div class="flex items-center">
                    <input
                      id="event_notifications"
                      type="checkbox"
                      checked
                      class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    >
                    <label for="event_notifications" class="ml-2 block text-sm text-gray-900">
                      School Events & Announcements
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {
  UserIcon,
  LockClosedIcon,
  CogIcon,
  BellIcon,
  CameraIcon,
  CheckCircleIcon
} from '@heroicons/vue/outline'

export default {
  name: 'ProfileIndex',
  components: {
    UserIcon,
    LockClosedIcon,
    CogIcon,
    BellIcon,
    CameraIcon,
    CheckCircleIcon
  },
  data() {
    return {
      activeTab: 'profile',
      user: {},
      form: {},
      passwordForm: {
        current_password: '',
        password: '',
        password_confirmation: ''
      },
      errors: {},
      passwordErrors: {},
      loading: false,
      passwordLoading: false,
      stats: {},
      loginHistory: [],
      tabs: [
        { id: 'profile', name: 'Profile', icon: UserIcon },
        { id: 'password', name: 'Password', icon: LockClosedIcon },
        { id: 'account', name: 'Account', icon: CogIcon },
        { id: 'notifications', name: 'Notifications', icon: BellIcon }
      ]
    }
  },
  computed: {
    isPasswordValid() {
      return this.passwordForm.password && 
             this.passwordForm.password_confirmation &&
             this.passwordForm.password === this.passwordForm.password_confirmation &&
             this.passwordForm.password.length >= 8
    },
    passwordStrength() {
      const password = this.passwordForm.password
      return {
        length: password.length >= 8,
        mixedCase: /[a-z]/.test(password) && /[A-Z]/.test(password),
        numbers: /\d/.test(password)
      }
    }
  },
  async mounted() {
    await this.fetchUser()
    await this.fetchStats()
    await this.fetchLoginHistory()
  },
  methods: {
    async fetchUser() {
      try {
        const response = await this.$http.get('/api/profile')
        this.user = response.data
        this.resetForm()
      } catch (error) {
        console.error('Error fetching user:', error)
      }
    },
    async fetchStats() {
      try {
        const response = await this.$http.get('/api/profile/stats')
        this.stats = response.data
      } catch (error) {
        console.error('Error fetching stats:', error)
      }
    },
    async fetchLoginHistory() {
      try {
        const response = await this.$http.get('/api/profile/login-history')
        this.loginHistory = response.data
      } catch (error) {
        console.error('Error fetching login history:', error)
      }
    },
    resetForm() {
      this.form = {
        first_name: this.user.first_name || '',
        last_name: this.user.last_name || '',
        email: this.user.email || '',
        phone: this.user.phone || '',
        date_of_birth: this.user.date_of_birth || '',
        gender: this.user.gender || '',
        address: this.user.address || '',
        grade_level: this.user.grade_level || '',
        section: this.user.section || '',
        qualification: this.user.qualification || '',
        specialization: this.user.specialization || '',
        bio: this.user.bio || '',
        emergency_contact_name: this.user.emergency_contact_name || '',
        emergency_contact_phone: this.user.emergency_contact_phone || '',
        emergency_contact_relation: this.user.emergency_contact_relation || '',
        medical_information: this.user.medical_information || '',
        allergies: this.user.allergies || '',
        special_needs: this.user.special_needs || '',
        photo: null
      }
      this.errors = {}
    },
    handlePhotoChange(event) {
      const file = event.target.files[0]
      if (file) {
        // Validate file size (2MB)
        if (file.size > 2 * 1024 * 1024) {
          this.$notify({
            type: 'error',
            title: 'Error',
            text: 'Image size must be less than 2MB'
          })
          return
        }

        // Validate file type
        if (!file.type.startsWith('image/')) {
          this.$notify({
            type: 'error',
            title: 'Error',
            text: 'Please select an image file'
          })
          return
        }

        this.form.photo = file
        // Create preview URL
        this.form.photo_url = URL.createObjectURL(file)
      }
    },
    async updateProfile() {
      this.loading = true
      this.errors = {}

      try {
        const formData = new FormData()
        Object.keys(this.form).forEach(key => {
          if (this.form[key] !== null && this.form[key] !== undefined) {
            formData.append(key, this.form[key])
          }
        })

        const response = await this.$http.post('/api/profile', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })

        this.user = response.data.user
        this.$store.commit('SET_USER', this.user)
        
        this.$notify({
          type: 'success',
          title: 'Success',
          text: 'Profile updated successfully'
        })
      } catch (error) {
        if (error.response?.status === 422) {
          this.errors = error.response.data.errors
        } else {
          this.$notify({
            type: 'error',
            title: 'Error',
            text: 'Failed to update profile'
          })
        }
      } finally {
        this.loading = false
      }
    },
    async updatePassword() {
      this.passwordLoading = true
      this.passwordErrors = {}

      try {
        await this.$http.put('/api/profile/password', this.passwordForm)

        this.passwordForm = {
          current_password: '',
          password: '',
          password_confirmation: ''
        }

        this.$notify({
          type: 'success',
          title: 'Success',
          text: 'Password updated successfully'
        })
      } catch (error) {
        if (error.response?.status === 422) {
          this.passwordErrors = error.response.data.errors
        } else {
          this.$notify({
            type: 'error',
            title: 'Error',
            text: 'Failed to update password'
          })
        }
      } finally {
        this.passwordLoading = false
      }
    },
    async toggleNotification(type) {
      try {
        const field = `${type}_notifications`
        const value = !this.user[field]
        
        await this.$http.patch('/api/profile/notifications', {
          [field]: value
        })

        this.user[field] = value
        this.$store.commit('SET_USER', this.user)
        
        this.$notify({
          type: 'success',
          title: 'Success',
          text: 'Notification preferences updated'
        })
      } catch (error) {
        this.$notify({
          type: 'error',
          title: 'Error',
          text: 'Failed to update notification preferences'
        })
      }
    },
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      })
    },
    formatDateTime(dateString) {
      return new Date(dateString).toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit'
      })
    }
  }
}
</script>