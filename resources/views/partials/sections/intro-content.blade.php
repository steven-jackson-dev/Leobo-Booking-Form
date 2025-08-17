@if(isset($content->hide_section) && $content->hide_section == 'no')
    <section @if(!empty($content->id)) id="{!! $content->id !!}" @endif class="leo-intro-content px-7 md:px-10 py-14 md:py-20 lg:py-24 @if(!empty($content->extra_class)){!! $content->extra_class !!}@endif">
        <div class="anim_fadeinup flex flex-col items-center">
            @if(!empty($content->heading))
                <h1 class="text-center text-lightSand opacity-0">{!! $content->heading !!}</h1>
            @endif
            @if(!empty($content->description))
                <div class="leo-content text-lightSand/80 mt-4 text-center max-w-[750px] mx-auto opacity-0">
                    <p>{!! $content->description !!}</p>
                </div>
            @endif
            @if(!empty($content->cta))
                <div class="leo-btn-wrapper mt-6 opacity-0">
                    <a href="{!! $content->cta['url'] !!}" target="{!! $content->cta['target'] !!}" class="leo-btn-secondary">{!! $content->cta['title'] !!}<span>{!! $content->cta['title'] !!}</span></a>
                </div>
            @endif
        </div>
    </section>
@endif
