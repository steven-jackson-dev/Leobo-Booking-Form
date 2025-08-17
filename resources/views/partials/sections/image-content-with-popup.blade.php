@if(isset($content->image) && $content->image)
<section class="px-7 md:px-10 py-14 md:py-20 lg:py-24 bg-maroon {!! $content->extra_class !!}" @if($content->extra_id) id="{{ $content->extra_id }}" @endif>
    <div class="flex flex-wrap items-center">
        <div class="lg:w-7/12 w-full @if($content->image_position == 'right')order-none lg:order-2 @endif">
            <div class="h-[90vw] md:h-[55vw]">
                <img src="{!! $content->image['url'] !!}" loading="lazy" class="h-full w-full object-cover" alt="{!! $content->image['title'] !!}">
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
                            <span class="btn leo-btn-tertiary leo-open-popup hover:cursor-pointer">{!! $content->button_two['title'] !!}</span>
                        @endif 
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endif

@if(!empty($content->popup_content_left))
    <section class="leo-popup fixed top-0 left-0 h-[100dvh] w-full z-[15]" style="display:none;">
        <img src="@asset('images/icons/close-white.svg')" loading="lazy" alt="" class="leo-close-popup absolute z-[19] left-7 md:left-[68px] top-7 md:top-[68px] hover:cursor-pointer" />
        @if(!empty($content->popup_image))  
            <img src="{!! $content->popup_image['url'] !!}" loading="lazy" alt="{!! $content->popup_image['alt'] !!}" class="leo-background-img absolute z-[15] top-0 left-0 w-full h-full object-cover object-center">
            <div class="leo-background-overlay bg-[#000000]/60 absolute top-0 left-0 h-full w-full z-[16]"></div>
        @endif
        <div class="leo-content-overflow-wrapper overflow-auto relative h-full w-full z-[17] flex flex-col items-center justify-start md:justify-center">
            <div class="leo-popup-content-wrapper relative flex flex-col items-left md:items-center pl-[40px] md:pl-0 justify-start md:justify-center h-auto py-16 md:h-full w-full z-[17]">
                @if(!empty($content->heading))
                    <h2 class="leo-hero-heading text-left md:text-center lg:!text-[48px]">{!! $content->heading !!}</h2>
                @endif
                <div class="leo-popup-content pt-8 flex flex-col md:flex-row gap-0 md:gap-16 px-7">
                    <div class="leo-popup-content__left leo-content">
                        {!! $content->popup_content_left !!}
                    </div>
                    @if(!empty($content->popup_content_right))
                        <div class="leo-popup-content__right leo-content">
                            {!! $content->popup_content_right !!}
                        </div>
                    @endif
                </div>
                <div class="leo-btn-wrapper pt-10">
                    <a href="/enquire" class="btn leo-btn-secondary"><span>Enquire</span>Enquire</a>
                </div>
            </div>
        </div>
    </section>
@endif