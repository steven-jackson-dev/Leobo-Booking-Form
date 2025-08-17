@if(isset($content->hide_section) && $content->hide_section == 'no')
    <section @if(!empty($content->id)) id="{!! $content->id !!}" @endif class="leo-highlights-slider-section relative bg-sand py-14 md:py-20 lg:py-24 @if(!empty($content->extra_class)){!! $content->extra_class !!}@endif">
        <img src="@asset('images/charcoal-bg-high-res.png')" loading="lazy" alt="" class="leo-bg-overlay absolute object-cover top-0 left-0 w-full h-full z-[6]">
        <div class="anim_fadeinup flex flex-col md:flex-row justify-between items-start md:items-end gap-7 lg:gap-14 px-7 md:px-20 lg:px-24 xl:px-28 relative z-[7]">
            @if(!empty($content->heading))
                <h1 class="text-offWhite max-w-[480px]">{!! $content->heading !!}</h1>
            @endif
            @if(!empty($content->description))
                <div class="leo-content max-w-[580px]">
                    <p class="text-offWhite">{!! $content->description !!}</p>
                </div>
            @endif
        </div>
        <div class="leo-highlights-slider-wrapper mt-10 md:mt-12 relative z-[7]">
            <div class="leo-highlights-slider swiper !h-[96vw] md:!h-[42vw]">   
                <div class="swiper-wrapper md:left-[-15%]">
                    @if(!empty($content->slider))
                        @foreach ( $content->slider as $image )
                            <div class="swiper-slide relative flex flex-col">
                                <div class="leo-highlights-slider__img-wrapper relative h-[calc(100%-54px)] w-full rounded-[5px]">
                                    <div class="leo-img-overlay absolute top-0 left-0 w-full h-full rounded-[5px] z-[2] transition-all duration-[300ms] ease-out"></div>
                                    <img src="{!! $image['url'] !!}" alt="{!! $image['alt'] !!}" class="relative z-[1] h-full w-full object-cover rounded-[5px]">
                                </div>
                                @if(!empty($image['alt']))
                                    <div class="leo-highlights-slider__desc h-[54px] font-body uppercase font-light text-offWhite tracking-[0.05em] leading-[54px] text-[15px]">{!! $image['alt'] !!}</div>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
            <div class="leo-highlights-slider__prev-btn swiper-button-prev !left-[28px] !top-[43%]">
                <img src="@asset('images/icons/arrow-left.svg')" loading="lazy" width="60" height="16" alt="slider previous arrow" class="leo-slider-arrow w-[60px]">
            </div>
            <div class="leo-highlights-slider__next-btn swiper-button-next !right-[28px] !top-[43%]">
                <img src="@asset('images/icons/arrow-right.svg')" loading="lazy" width="60" height="16" alt="slider next arrow" class="leo-slider-arrow w-[60px]">
            </div>
            <div class="leo-highlights-slider__bottom-bar flex items-center gap-8 md:gap-10 w-full px-7 md:px-0 md:ml-20 lg:ml-24 xl:ml-28 md:w-[350px] mt-7">
                @php
                    $numSlides = count($content->slider)
                @endphp
                <div class="leo-slide-count whitespace-nowrap font-body font-light text-white">
                    <div class="inline-block">0</div><span>1</span>&nbsp;&nbsp;/&nbsp;&nbsp;{!! $numSlides !!}
                </div>
                <div class="leo-highlights-slider__pagination flex justify-center !bg-offWhite/30 !h-[1px]"></div>
            </div>
        </div>
    </section>
@endif
