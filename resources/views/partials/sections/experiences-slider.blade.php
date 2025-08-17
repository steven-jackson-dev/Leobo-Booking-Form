@if(isset($content->hide_section) && $content->hide_section == 'no')
    <section @if(!empty($content->id)) id="{!! $content->id !!}" @endif class="leo-experiences-slider-section relative py-14 md:py-20 lg:py-24 @if(!empty($content->extra_class)){!! $content->extra_class !!}@endif">
        <div class="anim_fadeinup flex flex-col items-center px-7 md:px-20 lg:px-24 xl:px-28 relative z-[7]">
            @if(!empty($content->heading))
                <h1 class="text-lightSand max-w-[670px] mx-auto text-center">{!! $content->heading !!}</h1>
            @endif
            @if(!empty($content->description))
                <div class="leo-content max-w-[670px] mx-auto text-center mt-4">
                    <p class="text-lightSand/80">{!! $content->description !!}</p>
                </div>
            @endif
            @if(!empty($content->cta))
                <div class="leo-btn-wrapper mt-6 opacity-0">
                    <a href="{!! $content->cta['url'] !!}" target="{!! $content->cta['target'] !!}" class="leo-btn-secondary">{!! $content->cta['title'] !!}<span>{!! $content->cta['title'] !!}</span></a>
                </div>
            @endif
        </div>
        <div class="leo-experiences-slider-wrapper mt-10 md:mt-12 relative z-[7]">
            <div class="leo-experiences-slider swiper !h-[96vw] md:!h-[37vw]">   
                <div class="swiper-wrapper">
                        @foreach ( $content->experience_listing_arr as $item )
                            <div class="swiper-slide flex flex-col">
                                <a href="{!! $item['url'] !!}" class="leo-experiences-slider__media-wrapper block relative h-[calc(100%-54px)] w-full rounded-[5px]">
                                    <div class="leo-img-overlay absolute top-0 left-0 w-full h-full rounded-[5px] z-[10] transition-all duration-[300ms] ease-out"></div>
                                    @if(!empty($item['content']))  
                                        <video class="leo-background-video absolute rounded-[5px] z-[8] top-0 left-0 w-full h-full object-cover object-center" autoplay muted playsinline loop>
                                            <source src="{{$item['content']}}" type="video/mp4">
                                        </video>
                                    @endif
                                    <img src="{!! $item['img'] !!}" loading="lazy" alt="{!! $item['img_alt'] !!}" class="absolute rounded-[5px] z-[7] top-0 left-0 h-full w-full object-cover">
                                </a>
                                @if(!empty($item['url']))
                                    <a href="{!! $item['url'] !!}" class="leo-experiences-slider__desc h-[54px] font-body uppercase font-light text-center text-offWhite tracking-[0.05em] leading-[54px]">{!! $item['title'] !!}</a>
                                @endif
                            </div>
                        @endforeach
                </div>
            </div>
            <div class="leo-experiences-slider__prev-btn swiper-button-prev !left-[28px] !top-[43%]">
                <img src="@asset('images/icons/arrow-left.svg')" loading="lazy" width="60" height="16" alt="slider previous arrow" class="leo-slider-arrow w-[60px]">
            </div>
            <div class="leo-experiences-slider__next-btn swiper-button-next !right-[28px] !top-[43%]">
                <img src="@asset('images/icons/arrow-right.svg')" loading="lazy" width="60" height="16" alt="slider next arrow" class="leo-slider-arrow w-[60px]">
            </div>
            <div class="leo-experiences-slider__bottom-bar flex items-center gap-8 md:gap-10 w-full px-7 md:px-0 md:ml-10 md:w-[350px] mt-7">
                @php
                    $numSlides = count($content->experience_listing_arr)
                @endphp
                <div class="leo-slide-count whitespace-nowrap font-body font-light text-[#fff]">
                    <div class="inline-block">0</div><span>1</span>&nbsp;&nbsp;/&nbsp;&nbsp;{!! $numSlides !!}
                </div>
                <div class="leo-experiences-slider__pagination flex justify-center !bg-offWhite/30 !h-[1px]"></div>
            </div>
        </div>
    </section>
@endif