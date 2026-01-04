   <section class="py-12 sm:py-20 bg-white overflow-hidden">
       <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
           <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-8 items-center">

               {{-- Left Side: Phone Image --}}
               <div class="flex justify-center lg:justify-end order-last lg:order-first relative">
                   {{-- Optional decorative blob behind phone (Adjusted opacity for white bg) --}}
                   <div
                       class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-blue-600/10 rounded-full blur-3xl -z-10">
                   </div>

                   <img src="{{ asset('images/mobile3.png') }}" alt="Risk Track app interface"
                       class="max-w-sm sm:max-w-md lg:max-w-lg w-full h-auto drop-shadow-2xl">
               </div>

               {{-- Right Side: Content --}}
               <div class="text-center lg:text-left">
                   {{-- Changed text-white to text-gray-900 --}}
                   <h2 class="text-2xl sm:text-3xl md:text-4xl font-medium tracking-tight text-gray-900 mb-6">
                       Safety in Your Pocket
                   </h2>

                   {{-- Changed text-gray-300 to text-gray-600 --}}
                   <p
                       class="text-base sm:text-xl text-gray-600 font-medium leading-relaxed max-w-xl mx-auto lg:mx-0 mb-10">
                       See risks nearby and monitor live incidents in real-time.
                       Stay ahead of threats with instant alerts delivered directly to your device.
                   </p>

                   <div class="space-y-6">
                       {{-- Blue text usually works fine on white, kept as is, or change to text-blue-600 for darker contrast --}}
                       <p class="text-sm font-medium uppercase tracking-widest text-blue-600">
                           Scan code or click to download
                       </p>

                       {{-- Download Options Grid --}}
                       <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-6">

                           {{-- Option 1: Apple App Store --}}
                           {{-- Changed container style from translucent white to solid white with gray border/shadow --}}
                           <div
                               class="p-4 flex items-center gap-4 hover:shadow-md hover:border-gray-300 transition-all group">
                               {{-- QR Code container --}}
                               <div class="p-1 rounded-lg shrink-0 border border-gray-100">
                                   <img src="{{ asset('images/appstore-qr-code.png') }}" alt="Scan for iOS"
                                       class="w-20 h-20 object-contain">
                               </div>
                               {{-- Button & Text --}}
                               <div class="text-left">
                                   {{-- Changed text-gray-400 to text-gray-500 --}}
                                   <span class="block text-xs text-gray-500 mb-2">For iOS Devices</span>
                                   <a href="https://apps.apple.com/app/risktrack/id6739492527" target="_blank"
                                       class="block transition-transform hover:scale-105">
                                       <img src="images/apple.svg" alt="Download on the App Store" class="h-10 w-auto">
                                   </a>
                               </div>
                           </div>

                           {{-- Option 2: Google Play Store --}}
                           {{-- Changed container style --}}
                           <div
                               class="p-4 flex items-center gap-4 hover:shadow-md hover:border-gray-300 transition-all group">
                               {{-- QR Code container --}}
                               <div class="p-1 rounded-lg shrink-0 border border-gray-100">
                                   <img src="{{ asset('images/playstore-qr-code.png') }}" alt="Scan for Android"
                                       class="w-20 h-20 object-contain">
                               </div>
                               {{-- Button & Text --}}
                               <div class="text-left">
                                   {{-- Changed text-gray-400 to text-gray-500 --}}
                                   <span class="block text-xs text-gray-500 mb-2">For Android Devices</span>
                                   <a href="https://play.google.com/store/apps/details?id=com.risktrack.risktrack"
                                       target="_blank" class="block transition-transform hover:scale-105">
                                       <img src="images/google.svg" alt="Get it on Google Play" class="h-10 w-auto">
                                   </a>
                               </div>
                           </div>

                       </div>
                   </div>
               </div>

           </div>
       </div>
   </section>
