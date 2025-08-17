<section class="px-7 md:px-10 py-14 md:py-20 lg:py-24 @if($content->background_color === 'dark') bg-maroon @else bg-maroonLight @endif {!! $content->extra_class !!}" @if($content->extra_id) id="{{ $content->extra_id }}" @endif>
    <div class="flex flex-wrap items-center">
        <div class="lg:w-7/12 w-full @if($content->image_position == 'right')order-none lg:order-2 @endif">
            <div class="leo-media-content__content h-[70vw] md:h-[36vw] relative">
                @if(!empty($content->slider) && $content->media_type === 'slider')
                    <div class="leo-media-content-slider swiper h-[70vw] md:h-[36vw] relative">   
                        <div class="swiper-wrapper">
                            @if(!empty($content->slider))
                                @foreach ( $content->slider as $item )
                                    <div class="swiper-slide flex flex-col">
                                        <div class="leo-overlay absolute top-0 left-0 h-full w-full z-[8] bg-gradient-to-b from-black/0 to-black/30"></div>
                                        <img src="{!! $item['url'] !!}" loading="lazy" alt="{!! $item['alt'] !!}" class="absolute rounded-[5px] z-[7] top-0 left-0 h-full w-full object-cover object-center">
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        <div class="leo-slider-arrow-wrapper absolute !right-[28px] md:!right-[40px] !bottom-[20px] md:!bottom-[28px] !flex gap-10 md:gap-14">
                            <div class="leo-media-content__prev-btn swiper-button-prev !relative !left-0 !top-0">
                                <img src="@asset('images/icons/arrow-left.svg')" loading="lazy" width="60" height="16" alt="slider previous arrow" class="leo-slider-arrow w-[60px]">
                            </div>
                            <div class="leo-media-content__next-btn swiper-button-next !relative !right-0 !top-0">
                                <img src="@asset('images/icons/arrow-right.svg')" loading="lazy" width="60" height="16" alt="slider next arrow" class="leo-slider-arrow w-[60px]">
                            </div>
                        </div>
                    </div>
                @else
                    @if(!empty($content->video_url))  
                        <video class="leo-background-video absolute z-[2] top-0 left-0 w-full h-full object-cover object-center" autoplay muted playsinline loop>
                            <source src="{{$content->video_url}}" type="video/mp4">
                        </video>
                        @if(!empty($content->video_poster))  
                            <img src="{!! $content->video_poster['url'] !!}" loading="lazy" alt="{!! $content->video_poster['alt'] !!}" class="leo-background-img absolute z-[1] top-0 left-0 w-full h-full object-cover object-center">
                        @endif
                    @endif
                @endif
            </div>
        </div>
        <div class="lg:w-5/12 w-full">
            <div class="pb-0 md:px-[50px] pt-[40px] lg:pt-0 @if($content->image_position == 'right') xl:pr-120 @else xl:pl-120 @endif">
                <div class="lg:max-w-430 anim_fadeinup @if($content->image_position == 'right') lg:ml-auto @else lg:mr-auto @endif">
                    @if(isset($content->heading) && $content->heading)
                    <div class="title">
                        <h2 class="text-lightSand">{!! $content->heading !!}</h2>
                    </div>
                    @endif
                    <div class="leo-content text-lightSand/80 font-body opacity-80 pt-[20px] md:pt-10 max-w-[490px]">
                        {!! $content->description !!}
                    </div>
                    @if(isset($content->button) && $content->button)
                    <div class="leo-btn-wrapper !items-start pt-10">
                        @if(!empty($content->button))
                            <a href="{!! $content->button['url'] !!}" class="btn leo-btn-secondary" target="{{ $content->button['target'] ? $content->button['target'] : '_self' }}"><span>{!! $content->button['title'] !!}</span>{!! $content->button['title'] !!}</a>
                        @endif 
                        @if(!empty($content->button_two))
                            <a href="{!! $content->button_two['url'] !!}" class="btn leo-btn-tertiary " target="{{ $content->button_two['target'] ? $content->button_two['target'] : '_self' }}">{!! $content->button_two['title'] !!}</a>
                        @endif 
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>