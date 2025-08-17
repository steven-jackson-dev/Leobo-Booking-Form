<section class="accordion-wrapper py-[50px]">
  <div class="py-[70px] w-full">
    <div class="text-center">
      <h3>{!! $content->heading !!}</h3>
    </div>
    @php $iteration = 1 @endphp
    @foreach ($content->tabs as $tab)
      <div class="pt-10 accordion-content flex flex-col w-full px-7 md:px-0 md:w-7/12 mx-auto">
        <div class="accordion-item">
          <div class="accordion-title accordion faq-accordion cursor-pointer py-4 pr-10 border-b border-lightSand relative">
            <h6 class="text-lg font-semibold">{!! $tab['title'] !!}</h6>
            <span class="accordion-icon absolute right-0 top-0 bottom-0 flex items-center justify-center px-3">
              <svg class="w-6 h-6" width="13" height="13" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M5.82851 5.84728V0H7.17149V5.84728H13V7.15272H7.17149V13H5.82851V7.15272H0V5.84728H5.82851Z" fill="#EED1C0"/>
              </svg>
              <svg class="w-6 h-6 hidden" width="13" height="2" viewBox="0 0 13 2" fill="none" xmlns="http://www.w3.org/2000/svg">
                <line y1="1.4" x2="13" y2="1.4" stroke="#EED1C0" stroke-width="1.2"/>
              </svg>
            </span>
          </div>
          <div class="accordion-description hidden">
            <p class="pb-5">{!! $tab['description'] !!}</p>
          </div>
        </div>
      </div>
      @php $iteration += 1 @endphp
    @endforeach
  </div>
</section>