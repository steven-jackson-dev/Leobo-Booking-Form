<footer class="anim_fadeinup leo-footer w-full bg-maroon flex flex-col justify-between gap-10 h-[max-content] pt-16">

  <div class="flex justify-center">
      <a href="/">
        @if(!empty($footer_logo))
          <img src="{!! $footer_logo['url'] !!}" loading="lazy" alt="">
        @endif
      </a>
  </div>

  <div class="flex justify-center">
    @if(!empty($header_enquiry_button))
      <a href="{!! $header_enquiry_button['url'] !!}" target="{!! $header_enquiry_button['target'] !!}" class="leo-btn-primary block md:hidden">{!! $header_enquiry_button['title'] !!}</a>
    @endif
  </div>

  <div>
      <div class="flex flex-col items-center gap-5">
          <div
              class="text-[#EED1C0] uppercase text-[14px] font-bold flex cursor-pointer mt-3 md:mt-5">
              {!! wp_nav_menu(['theme_location' => 'primary_navigation', 'container' => false, 'echo' => false]) !!}
          </div>
          <div
              class="text-[#EED1C0] flex uppercase font-light text-[13px] cursor-pointer mt-5 lg:mt-1">
              {!! wp_nav_menu(['theme_location' => 'secondary_navigation', 'container' => false, 'echo' => false]) !!}
          </div>
      </div>
  </div>

  <div class="flex flex-col gap-3 md:gap-6">
    <div class="contact-details font-body font-light text-lightSand tracking-[0.05em]  text-[13px] uppercase text-center mt-3 md:mt-5">
      @if(!empty($header_phone_number)) <a class="hover:text-lightSand/80 transition-all duration-150 ease-out" href="tel:{!! $header_phone_number !!}">{!! $header_phone_number !!}</a> @endif | @if(!empty($header_email)) <a class="hover:text-lightSand/80 transition-all duration-150 ease-out" href="mailto:{!! $header_email !!}">{!! $header_email !!}</a> @endif
    </div>
    
      <div class="flex justify-center">
        @if(!empty($header_enquiry_button))
          <a href="{!! $header_enquiry_button['url'] !!}" target="{!! $header_enquiry_button['target'] !!}" class="leo-btn-primary hidden md:block">{!! $header_enquiry_button['title'] !!}</a>
        @endif
      </div>

      <div class="leo-socials flex justify-center gap-3 pb-4">
        @if(!empty($header_socials))
          @foreach ($header_socials as $item)
            <a href="{!! $item['link']['url'] !!}" target="{!! $item['link']['target'] !!}" class="hover:opacity-80 transition-all duration-150 ease-out">
              <img src="{!! $item['icon']['url'] !!}" loading="lazy" alt="{!! $item['icon']['alt'] !!}" class="leo-social-icon h-[20px] object-contain">
            </a>
          @endforeach
        @endif
      </div>
  </div>


  <div class="flex justify-center bg-[#2C1E21] py-4 px-7">
      <p class="font-light font-body text-[14px] text-lightSand/80 text-center">@if(!empty($footer_copyright_text)) {!! $footer_copyright_text !!} @endif | @if(!empty($footer_privacy_policy_link)) <a class="text-lightSand/80 hover:text-lightSand/100 transition-all duration-150 ease-out" href="{!! $footer_privacy_policy_link['url'] !!}" target="{!! $footer_privacy_policy_link['target'] !!}">{!! $footer_privacy_policy_link['title'] !!}</a> @endif | @if(!empty($footer_terms_link)) <a class="text-lightSand/80 hover:text-lightSand/100 transition-all duration-150 ease-out" href="{!! $footer_terms_link['url'] !!}" target="{!! $footer_terms_link['target'] !!}">{!! $footer_terms_link['title'] !!}</a> @endif</p>
  </div>

</footer>