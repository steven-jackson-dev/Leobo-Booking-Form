@if(isset($content->hide_section) && $content->hide_section == 'no')
    <section @if(!empty($content->id)) id="{!! $content->id !!}" @endif class="leo-awards-slider-section bg-maroonLight py-14 md:py-20 lg:py-24 @if(!empty($content->extra_class)){!! $content->extra_class !!}@endif">
        <div class="anim_fadeinup">
            @if(!empty($content->heading))
                <h5 class="text-center opacity-0 px-7">{!! $content->heading !!}</h5>
            @endif
            <div class="leo-awards-slider-wrapper mt-10 md:mt-12">
                <div class="leo-awards-slider swiper">   
                    <div class="swiper-wrapper">
                        @if(!empty($content->slider))
                            @foreach ( $content->slider as $image )
                                <div class="swiper-slide relative">
                                    <img src="{!! $image['url'] !!}" loading="lazy" alt="{!! $image['alt'] !!}" class="h-[100px] md:h-[130px] object-contain">
                                </div>
                            @endforeach
                            @if(count($content->slider) < 15)
                                @foreach ( $content->slider as $image )
                                    <div class="swiper-slide relative">
                                        <img src="{!! $image['url'] !!}" loading="lazy" alt="{!! $image['alt'] !!}" class="h-[100px] md:h-[130px] object-contain">
                                    </div>
                                @endforeach
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
