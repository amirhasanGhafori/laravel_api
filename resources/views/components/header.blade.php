<div class="container mt-4 mx-auto py-4 text-right" dir="rtl">
    <div class="mb-6 border-b border-gray-200">
        <nav class="flex gap-6">
            <a href="{{ route('users') }}"
                class="border-b-2 pb-3 text-sm font-medium transition
                      {{ request()->routeIs('users.*') ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }}">
                کاربران
            </a>

            <a href="{{ route('posts') }}"
                class="border-b-2 pb-3 text-sm font-medium transition
                      {{ request()->routeIs('posts.*') ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }}">
                پست‌ها
            </a>
        </nav>
    </div>

</div>
