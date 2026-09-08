@component('welcome')

    {{-- Navigation Header --}}

    <div class="space-y-4">

        {{-- Header --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-lg font-semibold text-gray-900">
                    پست‌ها
                </h1>
                <p class="mt-1 text-sm text-gray-500">
                    لیست پست‌ها و وضعیت انتشار
                </p>
            </div>

            <div class="flex items-center gap-3">
                <div class="text-sm text-gray-500">
                    تعداد:
                    <span class="font-semibold text-gray-900">
                        {{ $posts->total() }}
                    </span>
                </div>

                <a href=""
                   class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700">
                    + پست جدید
                </a>
            </div>
        </div>

        {{-- Search --}}
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <form method="GET" action="{{ url()->current() }}">
                <div class="flex flex-col gap-3 sm:flex-row">

                    <div class="relative flex-1">
                        <svg
                            class="pointer-events-none absolute right-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="m21 21-4.35-4.35m2.1-5.4a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z"
                            />
                        </svg>

                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="جستجو بر اساس عنوان یا محتوا..."
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 py-2.5 pr-10 pl-4 text-sm text-gray-900 outline-none transition focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-100"
                        >
                    </div>

                    <button
                        type="submit"
                        class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        جستجو
                    </button>

                    @if(request('search'))
                        <a
                            href="{{ url()->current() }}"
                            class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-center text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                        >
                            پاک کردن
                        </a>
                    @endif

                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

            <div class="overflow-x-auto">
                <table class="w-full text-right text-sm">

                    <thead class="bg-gray-50 text-xs font-semibold text-gray-600">
                        <tr>
                            <th class="whitespace-nowrap px-6 py-4">عنوان</th>
                            <th class="whitespace-nowrap px-6 py-4">نویسنده</th>
                            <th class="whitespace-nowrap px-6 py-4">وضعیت</th>
                            <th class="whitespace-nowrap px-6 py-4">تاریخ انتشار</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">

                        @forelse ($posts as $post)

                            <tr class="transition hover:bg-gray-50">

                                {{-- Title --}}
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">
                                        {{ $post->title }} <span class="bg-purple-400 p-2 text-xs text-white rounded-full">{{ number_format($post->score, 1) }}</span>
                                    </div>
                                    <div class="mt-1 text-xs text-gray-400">
                                        {{ $post->slug }}
                                    </div>
                                </td>

                                {{-- Author --}}
                                <td class="whitespace-nowrap px-6 py-4 text-gray-600">
                                    {{ $post->author->firstName .' '. $post->author->lastName }}
                                </td>

                                {{-- Status --}}
                                <td class="whitespace-nowrap px-6 py-4">
                                    @if($post->is_published)
                                        <span class="inline-flex rounded-full bg-green-50 px-3 py-1 text-xs font-medium text-green-700">
                                            منتشر شده
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-yellow-50 px-3 py-1 text-xs font-medium text-yellow-700">
                                            پیش‌نویس
                                        </span>
                                    @endif
                                </td>

                                {{-- Published At --}}
                                <td class="whitespace-nowrap px-6 py-4 text-gray-600">
                                    {{ $post->published_at?->format('Y/m/d H:i') ?? '—' }}
                                </td>

                           

                            </tr>

                        @empty

                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="mb-3 rounded-full bg-gray-100 p-3">
                                            <svg
                                                class="h-6 w-6 text-gray-400"
                                                xmlns="http://www.w3.org/2000/svg"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="m21 21-4.35-4.35m2.1-5.4a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z"
                                                />
                                            </svg>
                                        </div>

                                        <p class="text-sm font-medium text-gray-900">
                                            پستی پیدا نشد
                                        </p>

                                        <p class="mt-1 text-sm text-gray-500">
                                            عبارت جستجو را تغییر دهید یا پست جدیدی بسازید.
                                        </p>
                                    </div>
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>
            </div>

            {{-- Pagination --}}
            @if($posts->hasPages())
                <div class="border-t border-gray-100 px-6 py-4">
                    {{ $posts->withQueryString()->links() }}
                </div>
            @endif

        </div>

    </div>

@endcomponent