<header class="leo-header bg-transparent absolute top-0 left-0 z-[5] w-full h-[105px] flex items-center justify-between px-7 md:px-10">
  <button class="leo-menu-btn uppercase font-heading font-bold text-lightSand tracking-[0.05em] transition-all duration-150 ease-out hover:opacity-70">Menu</button>
  <a href="/" target="_blank" class="leo-main-logo-link h-[41px] w-[162px]">
    <img src="@asset('images/main-logo.svg')" alt="Logo" />
  </a>
</header>

<section class="leo-menu-modal fixed top-0 left-0 h-[100dvh] w-full z-[15] bg-maroon mobile-menu" style="display:none;">
  <img src="@asset('images/charcoal-bg-high-res.png')" alt="" class="leo-menu-modal-bg-img object-cover absolute top-0 left-0 w-full h-full z-[16]">
  <div class="leo-bg-overlay absolute top-0 left-0 w-full h-full z-[17] bg-maroon/80"></div>
  <div class="leo-top-bar relative z-[18] flex justify-between items-center h-[105px] px-7 md:px-10">
    <img src="@asset('images/icons/close-white.svg')" alt="" class="leo-close-menu-modal hover:cursor-pointer" />
    @if(!empty($header_enquiry_button))
      <a href="{!! $header_enquiry_button['url'] !!}" target="{!! $header_enquiry_button['target'] !!}" class="leo-btn-primary">{!! $header_enquiry_button['title'] !!}</a>
    @endif
  </div>
  <div class="leo-menu-modal-overflow-wrapper overflow-auto h-full">
    <div class="leo-menu-modal__inner relative z-[17] flex flex-col md:flex-row gap-7 pt-7 pb-32 px-7 md:px-10 max-w-[1140px] min-h-[600px] mx-auto">
      <div class="leo-menu-modal__left-col w-full md:w-[47%]">
        {!! wp_nav_menu(['theme_location' => 'primary_navigation', 'container' => false, 'echo' => false]) !!}
      </div>
      <div class="leo-menu-modal__right-col w-full md:w-[53%]">
        <div class="leo-menu-modal-media-wrapper leo-menu-modal-media-wrapper--desktop w-full h-[370px] hidden lg:block">
          @if(!empty($header_videos))
            @php $iteration = 1 @endphp
            @foreach ($header_videos as $item)
              <div id="{!! $item['video_id'] !!}" class="leo-menu-modal-media-wrapper-inner relative h-full w-full @if($iteration > 1) hidden @endif">
                @if(!empty($item['video_url']))
                  <video class="leo-menu-modal-video absolute z-[12] top-0 left-0 w-full h-full object-cover object-center" autoplay muted playsinline loop>
                      <source src="{{$item['video_url']}}" type="video/mp4">
                  </video>
                @endif
                @if(!empty($item['video_poster_image']))
                  <img src="{!! $item['video_poster_image']['url'] !!}" alt="{!! $item['video_poster_image']['alt'] !!}" class="leo-menu-modal-video__poster absolute z-[11] top-0 left-0 w-full h-full object-cover object-center">
                @endif
              </div>
              @php $iteration += 1 @endphp
            @endforeach
          @endif
        </div>
        <div class="leo-menu-modal-media-wrapper w-full h-[250px] lg:hidden">
          @if(!empty($header_videos))
            <div id="{!! $header_videos[0]['video_id'] !!}" class="leo-menu-modal-media-wrapper-inner relative h-full w-full">
              @if(!empty($header_videos[0]['video_url']))
                <video class="leo-menu-modal-video absolute z-[12] top-0 left-0 w-full h-full object-cover object-center" autoplay muted playsinline loop>
                    <source src="{{$header_videos[0]['video_url']}}" type="video/mp4">
                </video>
              @endif
              @if(!empty($header_videos[0]['video_poster_image']))
                <img src="{!! $header_videos[0]['video_poster_image']['url'] !!}" alt="{!! $header_videos[0]['video_poster_image']['alt'] !!}" class="leo-menu-modal-video__poster absolute z-[11] top-0 left-0 w-full h-full object-cover object-center">
              @endif
            </div>
          @endif
        </div>
        <div class="mt-6">
          {!! wp_nav_menu(['theme_location' => 'secondary_navigation', 'container' => false, 'echo' => false]) !!}
        </div>
        <div class="contact-details font-body font-light text-lightSand tracking-[0.05em]  text-[13px] uppercase text-center mt-7">
          @if(!empty($header_phone_number)) <a class="hover:text-lightSand/80 transition-all duration-150 ease-out" href="tel:{!! $header_phone_number !!}">{!! $header_phone_number !!}</a> @endif | @if(!empty($header_email)) <a class="hover:text-lightSand/80 transition-all duration-150 ease-out" href="mailto:{!! $header_email !!}">{!! $header_email !!}</a> @endif
        </div>
        <div class="leo-socials flex justify-center gap-3 mt-5">
          @if(!empty($header_socials))
            @foreach ($header_socials as $item)
              <a href="{!! $item['link']['url'] !!}" target="{!! $item['link']['target'] !!}" class="hover:opacity-80 transition-all duration-150 ease-out">
                <img src="{!! $item['icon']['url'] !!}" alt="{!! $item['icon']['alt'] !!}" class="leo-social-icon h-[20px] object-contain">
              </a>
            @endforeach
          @endif
        </div>
      </div>
    </div>
  </div>
</section>

<main id="main" class="main font-body bg-maroon text-lightSand">
    <section class="leo-hero h-[100dvh] relative w-full">
        <div class="leo-hero-content absolute top-0 left-0 h-full w-full z-[3] flex flex-col  px-7 md:px-10 py-20 md:py-10 items-center justify-center">
            <div class="bannerTxt_anim">
                @if(!empty($not_found_heading))
                    <h1 class="leo-hero-heading text-center opacity-0">{!! $not_found_heading !!}</h1>
                @endif
                @if(!empty($not_found_subheading))
                    <div class="leo-content text-lightSand/80 opacity-0 max-w-[400px] text-center font-cta font-normal mt-5">
                        <p>{!! $not_found_subheading !!}</p>
                    </div>
                @endif
                @if(!empty($not_found_cta))
                    <div class="leo-btn-wrapper mt-8 opacity-0 flex justify-center w-full">
                        <a href="{!! $not_found_cta['url'] !!}" target="{!! $not_found_cta['target'] !!}" class="leo-btn-primary">{!! $not_found_cta['title'] !!}</a>
                    </div>
                @endif
            </div>
        </div>
        <div class="leo-background absolute top-0 left-0 h-full w-full z-[1]">
            <div class="leo-background-overlay bg-[#000000]/20 absolute top-0 left-0 h-full w-full z-[2]"></div>
            @if(!empty($not_found_background_image))  
                <img src="{!! $not_found_background_image['url'] !!}" alt="{!! $not_found_background_image['alt'] !!}" class="leo-background-img absolute z-[1] top-0 left-0 w-full h-full object-cover object-center hidden md:block">
            @endif
            @if(!empty($not_found_mobile_background_image))  
                <img src="{!! $not_found_mobile_background_image['url'] !!}" alt="{!! $not_found_mobile_background_image['alt'] !!}" class="leo-background-img--mobile absolute z-[1] top-0 left-0 w-full h-full object-cover object-center md:hidden">
            @endif
        </div>
    </section>


</main>
