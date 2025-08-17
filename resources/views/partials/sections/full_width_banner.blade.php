<section
    class="leo-full-width-banner bg-maroonLight px-7 md:px-10 py-14 md:py-20 lg:py-24 flex flex-col items-center h-[400px] md:h-[550px] lg:h-[736px] relative w-full">
    <div
        class="h-full relative w-full">
        @if(!empty($content->background_image))  
                <img src="{!! $content->background_image['url'] !!}" loading="eager" alt="{!! $content->background_image['alt'] !!}" class="leo-background-img absolute top-0 left-0 w-full h-full object-cover object-center hidden md:block">
        @endif
        <div class="text-right w-full md:w-[660px] absolute bottom-[64px] md:right-[80px] anim_fadeinup">
            @if (isset($content->heading) && $content->heading)
                <h2>{!! $content->heading !!}</h2>
            @endif
            @if (isset($content->description) && $content->description)
                <div class="leo-content">
                    <p class="pt-5 !text-lightSand/80">{!! $content->description !!}</p>
                </div>
            @endif
            @if (!empty($content->button))
                <div class="pt-5">
                    <a class="btn leo-btn-secondary whitespace-nowrap" href="{!! $content->button['url'] !!}"
                        target="{!! $content->button['target'] !!}">
                        <span>{!! $content->button['title'] !!}</span>
                        {!! $content->button['title'] !!}
                    </a>
                </div>
            @endif
        </div>
    </div>

</section>
