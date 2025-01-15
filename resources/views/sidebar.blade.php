<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sidebar Example</title>
    @vite('resources/css/app.css')
</head>

<body class="bg-gray-100">
    @include('sidebar', ['menus' => $menus])


    <!-- Sidebar -->
    <div id="sidebar" class="flex">
        <div class="bg-dark-purple p-5 pt-8 duration-300 fixed top-0 bottom-0 left-0 z-10" :class="{ 'w-60': sidebarVisible, 'w-20': !sidebarVisible }">
            <i class="fa-solid fa-arrow-left text-center bg-white text-dark-purple text-2xl w-8 rounded-full absolute -right-3 top-9 border border-dark-purple cursor-pointer" @click="toggleSidebar" :class="{ 'rotate-180': !sidebarVisible }"></i>

            <!-- Sidebar Content -->
            <div class="flex items-center justify-center">
                <i class="fa-solid fa-g text-2xl font-bold text-white duration-500" :class="{ 'rotate-[360deg]': !sidebarVisible }"></i>
                <h1 class="text-white origin-left font-medium duration-300" :class="{ 'hidden': !sidebarVisible }">- CHEQUE</h1>
            </div>

            <hr class="text-light-white" :class="{ 'w-10': !sidebarVisible, 'w-50': sidebarVisible }" />
            <ul class="flex flex-col gap-6 mt-14">
                @foreach($menus as $menu)
                <li class="relative flex justify-center items-center p-2 rounded-md hover:bg-light-white cursor-pointer text-white gap-2">
                    <i class="fa-solid {{ $menu['icon'] }}"></i>
                    <span class="font-medium flex-1 duration-200" :class="{ 'hidden': !sidebarVisible }">{{ $menu['title'] }}</span>
                </li>
                @endforeach
            </ul>
        </div>

        <!-- Main Content -->
        <div class="flex flex-col w-full" :class="{ 'ml-60': sidebarVisible, 'ml-20': !sidebarVisible }">
            <div class="p-5 bg-dark-purple w-full h-16 duration-300 fixed top-0 left-0 right-0 z-1" :class="{ 'ml-60': sidebarVisible, 'ml-20': !sidebarVisible }">
                <span class="text-white font-medium">
                    <i class="fa-solid fa-house"></i> Accueil
                </span>
            </div>

            <!-- Content router-outlet -->
            <div class="px-4 duration-300 py-24">
                @yield('content')
            </div>
        </div>
    </div>

    <script src="{{ mix('js/app.js') }}"></script>
</body>

</html>
