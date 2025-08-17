<section
    class="leo-stacked-media-content anim_fadeinup md:px-10 py-14 md:py-20 lg:py-24 flex flex-col items-center h-[70dvh] lg:h-[100dvh] relative w-full">
    <div
        class="flex flex-col lg:flex-row gap-10 md:gap-20 w-full lg:w-full h-full justify-center px-0 md:px-10 items-start @if (!empty($content->type == 'reverse')) w-full !flex-row-reverse @endif">
        <div class="lg:w-6/12 w-full order-2 lg:order-1 @if (!empty($content->type == 'reverse')) pl-0 @endif">
            <div class="relative w-full h-[342px]">
                @if (!empty($content->video_url))
                    <video
                        class="leo-background-video absolute w-full h-[342px] z-[2] top-0 left-0 object-cover object-center"
                        autoplay muted playsinline loop>
                        <source src="{{ $content->video_url }}" type="video/mp4">
                    </video>
                @endif

                @if (isset($content->small_image))
                    <img src="{!! $content->small_image['url'] !!}" class="w-full h-[342px]" width="660"
                        height="342" />
                @endif
            </div>

            @if (isset($content->heading) && $content->heading)
                <div class="relative pt-10 px-7 md:px-0">
                    <h2>{!! $content->heading !!}</h2>
                </div>
            @endif

            @if (isset($content->description) && $content->description)
                <div class="relative pt-5 px-7 md:px-0">
                    <p>{!! $content->description !!}</p>
                </div>
            @endif

            @if (!empty($content->button))
                <div class="relative pt-10 leo-btn-wrapper px-7 md:px-0">
                    <a class="leo-btn-secondary !text-lightSand" href="{!! $content->button['url'] !!}">
                        {!! $content->button['title'] !!}
                    </a>
                    @if (!empty($content->popup_button))
                        <span id="dome-open-popup" class="leo-btn-tertiary !text-lightSand hover:cursor-pointer">
                            {!! $content->popup_button['title'] !!}
                        </span>
                    @endif
                </div>
            @endif
        </div>

        <div class="lg:w-6/12 w-full order-1 lg:order-2 lg:block hidden">
            @if (isset($content->large_image))
                <img src="{!! $content->large_image['url'] !!}" class="w-full h-[654px]" width="616"
                    height="654" />
            @endif
        </div>
    </div>
</section>

@if(!empty($content->popup_button))
    <section class="dome-popup bg-[#fff] fixed top-0 left-0 h-[100dvh] w-full z-[15]" style="display:none;">
        <img src="@asset('images/icons/close-white.svg')" loading="lazy" alt="" class="dome-close-popup absolute z-[19] left-7 md:left-[68px] top-7 md:top-[68px] hover:cursor-pointer" />
        <div class="leo-content-overflow-wrapper overflow-auto relative h-full w-full z-[17] flex items-center justify-center">
            @if(!empty($content->popup_image))
                <img src="{!! $content->popup_image['url'] !!}" alt="{!! $content->popup_image['alt'] !!}" class="leo-dome-img h-[85vh] px-[20px] md:px-0 object-contain">
            @endif
        </div>
    </section>
@endif
