@if(isset($content->image) && $content->image)
<section class="px-7 md:px-10 py-14 md:py-20 lg:py-24 @if($content->background_color === 'dark') bg-maroon @else bg-maroonLight @endif {!! $content->extra_class !!}" @if($content->extra_id) id="{{ $content->extra_id }}" @endif>
    <div class="flex flex-wrap items-center">
        <div class="lg:w-7/12 w-full @if($content->image_position == 'right')order-none lg:order-2 @endif">
            <div class="h-[70vw] md:h-[36vw] rounded-[5px]">
                <img src="{!! $content->image['url'] !!}" loading="lazy" class="rounded-[5px] block h-full w-full object-center object-cover" alt="{!! $content->image['title'] !!}">
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
                    <div class="leo-content text-lightSand/80 font-body opacity-80 pt-[20px] md:pt-7">
                        {!! $content->description !!}
                    </div>
                    @if(isset($content->button) && $content->button)
                    <div class="flex flex-col md:flex-row md:items-center">
                     @if(!empty($content->button))
                            <div class="pt-[20px] md-[40px] lg-[40px]">
                                <a href="{!! $content->button['url'] !!}" class="btn leo-btn-secondary" target="{{ $content->button['target'] ? $content->button['target'] : '_self' }}"><span>{!! $content->button['title'] !!}</span>{!! $content->button['title'] !!}</a>
                            </div>
                        @endif 
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endif