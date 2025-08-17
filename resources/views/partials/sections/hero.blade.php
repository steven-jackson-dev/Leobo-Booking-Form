@if(isset($content->hide_section) && $content->hide_section == 'no')
    <section @if(!empty($content->id)) id="{!! $content->id !!}" @endif class="leo-hero h-[80dvh] lg:h-[100dvh] @if($content->hero_type !== 'home') leo-hero--home max-h-[800px] @endif relative w-full @if(!empty($content->extra_class)){!! $content->extra_class !!}@endif">
        <div class="leo-hero-content absolute top-0 left-0 h-full w-full z-[3] flex flex-col  px-7 md:px-10 pb-20 md:pb-10 @if($content->hero_type === 'home') items-center justify-start mt-24 @elseif($content->hero_type === 'single') items-center justify-end pb-16 md:pb-24 @else items-start justify-end  @endif">
            <a href="/" class="@if(!empty($content->hero_type === 'home')) relative @else absolute top-0 mt-8 right-7 md:right-[unset] md:left-[50%] md:translate-x-[-50%] @endif">
                <img src="@asset('images/main-logo.svg')" loading="eager" alt="Leobo main logo" class="main-logo @if(!empty($content->hero_type === 'home')) h-[83px] object-contain main-logo--home @else h-[41px] object-contain  @endif">
            </a>
            <div class="bannerTxt_anim">
                @if(!empty($content->pre_heading) && $content->hero_type === 'single')
                    <div class="leo-content text-lightSand text-[22px] opacity-0 text-center font-cta font-normal mb-5">
                        <p>{!! $content->pre_heading !!}</p>
                    </div>
                @endif
                @if(!empty($content->heading) && $content->hero_type !== 'home')
                    <h1 class="leo-hero-heading opacity-0">{!! $content->heading !!}</h1>
                @endif
                @if(!empty($content->sub_heading) && $content->hero_type !== 'single')
                    <div class="leo-content text-lightSand/80 opacity-0 @if($content->hero_type === 'home') subheading--home max-w-[400px] text-center font-cta font-normal mt-5 @else max-w-[600px] lg:max-w-[700px] mt-2 @endif">
                        <p>{!! $content->sub_heading !!}</p>
                    </div>
                @endif
            </div>
        </div>
        <div class="leo-background absolute top-0 left-0 h-full w-full z-[1]">
            <div class="leo-background-overlay bg-[#000000]/20 absolute top-0 left-0 h-full w-full z-[2]"></div>
            
            @if(!empty($content->background_image))  
                <img src="{!! $content->background_image['url'] !!}" loading="eager" alt="{!! $content->background_image['alt'] !!}" class="leo-background-img absolute z-[1] top-0 left-0 w-full h-full object-cover object-center hidden md:block">
            @endif

            @if(!empty($content->mobile_background_image))  
                <img src="{!! $content->mobile_background_image['url'] !!}" loading="eager" fetchpriority="high" alt="{!! $content->mobile_background_image['alt'] !!}" class="leo-background-img--mobile absolute z-[1] top-0 left-0 w-full h-full object-cover object-center md:hidden">
            @else
                @if(!empty($content->background_image))  
                    <img src="{!! $content->background_image['url'] !!}" loading="eager" alt="{!! $content->background_image['alt'] !!}" class="leo-background-img--mobile absolute z-[1] top-0 left-0 w-full h-full object-cover object-center md:hidden">
                @endif
            @endif

            @if(!empty($content->background_video_url))  
                <video class="leo-background-video absolute z-[1] top-0 left-0 w-full h-full object-cover object-center" autoplay muted playsinline loop>
                    <source src="{{$content->background_video_url}}" type="video/mp4">
                </video>
            @endif
        </div>
        @if(!empty($content->full_video_url)) 
            <button class="play-in-lightbox btn btn--iconBtn absolute bottom-7 left-7 md:left-[unset] md:right-10 md:bottom-10 z-[3]">
                Watch Video
                <img src="@asset('images/icons/watch-video-icon.svg')" loading="eager" class="btn--iconBtn__icon " alt="">
                <span class="video-src hidden" data-src="{{ $content->full_video_url ?? '' }}"></span>
            </button>
        @endif
    </section>
    @if(!empty($content->full_video_url))
        <section class="video-lightbox" style="display:none;">
            <div class="video-lightbox__inner">
            <img src="@asset('images/icons/close-white.svg')" loading="lazy" alt="" class="video-lightbox__close"/>
            <video class="video-lightbox__video" controls type="video/mp4"></video>
            </div>      
        </section>
    @endif
@endif