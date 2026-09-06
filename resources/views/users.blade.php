@component('welcome')

    <div class="space-y-4">

        {{-- Header --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-lg font-semibold text-gray-900">
                    کاربران
                </h1>
                <p class="mt-1 text-sm text-gray-500">
                    لیست کاربران و اطلاعات آخرین ورود
                </p>
            </div>

            <div class="text-sm text-gray-500">
                تعداد:
                <span class="font-semibold text-gray-900">
                    {{ $users->total() }}
                </span>
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
                            placeholder="جستجو بر اساس نام یا ایمیل..."
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
                            <th class="whitespace-nowrap px-6 py-4">
                                نام 
                            </th>

                            <th class="whitespace-nowrap px-6 py-4">
                                ایمیل  
                            </th>

                            <th class="whitespace-nowrap px-6 py-4">
                                آخرین ورود
                            </th>

                            <th class="whitespace-nowrap px-6 py-4">
                                شرکت
                            </th>

                            <th class="whitespace-nowrap px-6 py-4">
                                ایمیل شرکت
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">

                        @forelse ($users as $user)

                            <tr class="transition hover:bg-gray-50">

                                <td class="whitespace-nowrap px-6 py-4 font-medium text-gray-900">
                                    {{ $user->firstName . ' ' . $user->lastName }}
                                </td>

                                <td class="px-6 py-4 text-gray-600">
                                    {{ $user->email }}
                                </td>

                                <td class="whitespace-nowrap px-6 py-4">
                                    @if($user->lastLogin)
                                        <div class="text-gray-900">
                                            {{ $user->lastLogin->created_at?->diffForHumans() }}
                                        </div>

                                        @if($user->lastLogin->ip_address)
                                            <div class="mt-1 text-xs text-gray-400">
                                                {{ $user->lastLogin->ip_address }}
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-gray-400">
                                            بدون ورود
                                        </span>
                                    @endif
                                </td>

                                <td class="px-6 py-4">
                                    @if($user->company)
                                        <span class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-600">
                                            {{ $user->company->name }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">
                                            —
                                        </span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 text-gray-600">
                                    {{ $user->company?->email ?? '—' }}
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
                                            کاربری پیدا نشد
                                        </p>

                                        <p class="mt-1 text-sm text-gray-500">
                                            عبارت جستجو را تغییر دهید.
                                        </p>
                                    </div>
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>
            </div>

            {{-- Pagination --}}
            @if($users->hasPages())
                <div class="border-t border-gray-100 px-6 py-4">
                    {{ $users->withQueryString()->links() }}
                </div>
            @endif

        </div>

    </div>

@endcomponent