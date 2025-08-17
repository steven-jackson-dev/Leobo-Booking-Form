@if(isset($content->hide_section) && $content->hide_section == 'no')
<section class="px-7 md:px-10 py-14 md:py-20 lg:py-24 bg-maroon">
  <div class="anim_fadeinup flex flex-col items-center px-0 md:px-20 lg:px-24 xl:px-28 relative z-[7]">
            @if(!empty($content->heading))
                <h1 class="text-lightSand max-w-[900px] mx-auto text-center">{!! $content->heading !!}</h1>
            @endif
            @if(!empty($content->description))
                <div class="leo-content max-w-[1000px] mx-auto text-center mt-4">
                    <div class="text-lightSand/80 font-body">{!! $content->description !!}</div>
                </div>
            @endif
            @if(!empty($content->cta))
                <div class="leo-btn-wrapper mt-6 opacity-0">
                    <a href="{!! $content->cta['url'] !!}" target="{!! $content->cta['target'] !!}" class="leo-btn-secondary">{!! $content->cta['title'] !!}<span>{!! $content->cta['title'] !!}</span></a>
                </div>
            @endif
        </div>
    <div class="container-fluid z-9 pt-14 px-0 h-full">
        <div class="video-bx z-99 relative">
            @if(!empty($content->bg_image))
                <img src="{!! $content->bg_image['url'] !!}"
                    class="md:block w-full h-[400px] md:h-[668px] object-cover" width="1500" height="1500" loading="lazy"
                    alt="{!! $content->bg_image['alt'] !!}">
            @endif 
            @if(!empty($content->video))
                <div class="video-container absolute top-0 left-0 w-full h-full">
                    <video id="myVideo" class="w-full h-full object-cover" autoplay muted playsinline loop poster="{!! $content->bg_image['url'] !!}">
                        <source src="{!! $content->video['url'] !!}" type="video/mp4">
                    </video>
                </div>
            @endif
        </div>
    </div>
</section>
@endif
