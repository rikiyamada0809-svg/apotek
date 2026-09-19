<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Login - Invision Hospital Management System</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-[#dce3f0] min-h-screen flex items-center justify-center p-4">
        <!-- Main Card -->
        <div class="bg-white rounded-[24px] shadow-2xl overflow-hidden max-w-5xl w-full min-h-[600px] flex flex-col md:flex-row">
            
            <!-- Left Column (Visuals & Branding) -->
            <div class="w-full md:w-1/2 bg-[#2f5ad8] flex flex-col text-white">
                <!-- Image -->
                <div class="h-64 md:h-[350px] w-full overflow-hidden">
                    <img src="/images/doctor_portrait.jpg" alt="Doctor" class="w-full h-full object-cover">
                </div>
                <!-- Brand Promo -->
                <div class="p-8 md:p-12 flex-1 flex flex-col justify-center">
                    <div class="flex items-center gap-3 mb-6">
                        <!-- Logo -->
                        <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center">
                            <svg class="w-5 h-5 text-[#2f5ad8]" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 15.5c-3.03 0-5.5-2.47-5.5-5.5s2.47-5.5 5.5-5.5 5.5 2.47 5.5 5.5-2.47 5.5-5.5 5.5zm1.5-8.5c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                            </svg>
                        </div>
                        <span class="text-2xl font-bold tracking-wide">Invision</span>
                    </div>
                    
                    <div class="border-l-4 border-white pl-4 my-2">
                        <h2 class="text-xl md:text-2xl font-bold leading-tight">
                            Welcome to <span class="font-extrabold text-blue-200">Invision</span><br>
                            Hospital Management System
                        </h2>
                    </div>
                    <p class="text-sm text-blue-100 mt-4 leading-relaxed max-w-sm">
                        Cloud Based Streamline Hospital Management system with centralized user friendly platform.
                    </p>
                </div>
            </div>

            <!-- Right Column (Login Form) -->
            <div class="w-full md:w-1/2 p-8 md:p-12 flex flex-col justify-center bg-white">
                <!-- Mobile Logo -->
                <div class="flex items-center gap-3 mb-8 md:mb-12">
                    <!-- Logo -->
                    <div class="w-8 h-8 rounded-full bg-[#2f5ad8] flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 15.5c-3.03 0-5.5-2.47-5.5-5.5s2.47-5.5 5.5-5.5 5.5 2.47 5.5 5.5-2.47 5.5-5.5 5.5zm1.5-8.5c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                        </svg>
                    </div>
                    <span class="text-2xl font-bold tracking-wide text-[#2f5ad8]">Invision</span>
                </div>

                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Login</h1>
                <p class="text-sm text-gray-500 mt-2">Enter your credentials to login to your account</p>

                <!-- Session Status -->
                <x-auth-session-status class="mt-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
                    @csrf

                    <!-- Email Address -->
                    <div class="space-y-1">
                        <label for="email" class="block text-sm font-semibold text-gray-700">Email</label>
                        <input id="email" 
                               type="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               required 
                               autofocus 
                               autocomplete="username" 
                               placeholder="example.nazarbecks@gmail.com" 
                               class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out text-sm text-gray-900 placeholder-gray-400">
                        <x-input-error :messages="$errors->get('email')" class="mt-1" />
                    </div>

                    <!-- Password -->
                    <div class="space-y-1" x-data="{ show: false }">
                        <label for="password" class="block text-sm font-semibold text-gray-700">Password</label>
                        <div class="relative">
                            <input id="password" 
                                   :type="show ? 'text' : 'password'" 
                                   name="password" 
                                   required 
                                   autocomplete="current-password" 
                                   placeholder="••••••••••••••••••••••••" 
                                   class="w-full pl-4 pr-10 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out text-sm text-gray-900 placeholder-gray-400">
                            <!-- Toggle Button -->
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition">
                                <!-- Eye Icon -->
                                <svg x-show="!show" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <!-- Eye Off Icon -->
                                <svg x-show="show" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-1" />
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between">
                        <label for="remember_me" class="inline-flex items-center">
                            <input id="remember_me" type="checkbox" name="remember" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                            <span class="ms-2 text-xs text-gray-600">Remember me</span>
                        </label>
                        @if (Route::has('password.request'))
                            <a class="text-xs font-semibold text-blue-600 hover:text-blue-800 transition" href="{{ route('password.request') }}">
                                Forgot Password?
                            </a>
                        @endif
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button type="submit" class="w-full bg-[#2f5ad8] hover:bg-[#254cc4] text-white py-3 rounded-lg font-semibold text-sm shadow-md hover:shadow-lg transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Sign In
                        </button>
                    </div>

                    <!-- Sign Up -->
                    <div class="text-center mt-6">
                        <span class="text-xs text-gray-600">Don't have an account? </span>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="text-xs font-bold text-[#2f5ad8] hover:text-[#254cc4] transition">
                                Sign Up
                            </a>
                        @endif
                    </div>
                </form>
            </div>

        </div>
    </body>
</html>
