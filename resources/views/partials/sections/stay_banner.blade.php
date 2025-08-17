@if(isset($content->hide_section) && $content->hide_section == 'no')
    <section @if(!empty($content->extra_id)) extra_id="{!! $content->id !!}" @endif class="leo-stay-banner relative bg-sand py-14 md:py-20 lg:py-24 @if(!empty($content->extra_class)){!! $content->extra_class !!}@endif">
        <img src="@asset('images/charcoal-bg-high-res.png')" loading="lazy" alt="" class="leo-bg-overlay absolute object-cover top-0 left-0 w-full h-full z-[6]">
        <div class="anim_fadeinup flex flex-col md:flex-row justify-center items-start px-7 md:px-20 lg:px-24 xl:px-28 relative z-[7]">
            @if(!empty($content->heading))
                <h1 class="text-offWhite">{!! $content->heading !!}</h1>
            @endif
        </div>
        <div class="leo-stay-banner-slider-wrapper mt-10 md:mt-12 relative z-[7] px-7 md:px-14">
            <div class="leo-stay-banner-slider swiper !h-[400px] md:!h-[700px]">   
                <div class="swiper-wrapper">
                    @if(!empty($content->slider))
                        @foreach ( $content->slider as $image )
                            <div class="swiper-slide relative flex flex-col">
                                <img src="{!! $image['url'] !!}" alt="{!! $image['alt'] !!}" loading="lazy" class="h-[700px] w-full object-cover rounded-[5px]">
                                @if(!empty($image['alt']))
                                    <div class="leo-stay-banner-slider__desc h-[54px] font-body uppercase font-light text-offWhite tracking-[0.05em] leading-[54px]">{!! $image['alt'] !!}</div>
                                @endif
                            </div>
                        @endforeach
                        @if(count($content->slider) < 8)
                            @foreach ( $content->slider as $image )
                                <div class="swiper-slide relative flex flex-col">
                                    <img src="{!! $image['url'] !!}" loading="lazy" alt="{!! $image['alt'] !!}" class="h-[700px] w-full object-cover rounded-[5px]">
                                    @if(!empty($image['alt']))
                                        <div class="leo-stay-banner-slider__desc h-[54px] font-body uppercase font-light text-offWhite tracking-[0.05em] leading-[54px]">{!! $image['alt'] !!}</div>
                                    @endif
                                </div>
                            @endforeach
                        @endif
                    @endif
                </div>
            </div>
        </div>
            <div class="leo-stay-banner-slider__prev-btn swiper-button-prev !left-[56px] md:!left-[80px] !top-[50%]">
                <img src="@asset('images/icons/arrow-left.svg')" loading="lazy" width="60" height="16" alt="slider previous arrow" class="leo-slider-arrow w-[60px]">
            </div>
            <div class="leo-stay-banner-slider__next-btn swiper-button-next !right-[56px] md:!right-[80px] !top-[50%]">
                <img src="@asset('images/icons/arrow-right.svg')" loading="lazy" width="60" height="16" alt="slider next arrow" class="leo-slider-arrow w-[60px]">
            </div>
        </div>
        <div class="flex flex-col md:flex-row justify-between items-start md:pt-[30px] gap-7 lg:gap-14 px-7 md:px-20 lg:px-24 xl:px-28 relative z-[7]">
            @if(!empty($content->description))
                <div class="leo-content max-w-[580px]">
                    <p class="text-offWhite font-body">{!! $content->description !!}</p>
                </div>
            @endif
            @if(!empty($content->button))
                <div>
                    <a href="{!! $content->button['url'] !!}" class="btn leo-btn-secondary-white " target="{{ $content->button['target'] ? $content->button['target'] : '_self' }}">{!! $content->button['title'] !!}</a>
                </div>
            @endif 
        </div>
    </section>
@endif
