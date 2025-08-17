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
                <div class="lg:max-w-430 anim_fadeinup  @if($content->image_position == 'right') lg:ml-auto @else lg:mr-auto @endif">
                    @if(isset($content->heading) && $content->heading)
                    <div class="title">
                        <h2 class="text-lightSand">{!! $content->heading !!}</h2>
                    </div>
                    @endif
                    <div class="leo-content text-lightSand/80 font-body opacity-80 pt-[20px] md:pt-10 max-w-[490px]">
                        {!! $content->description !!}
                    </div>

                    @if(isset($content->tabs) && is_array($content->tabs) && count($content->tabs) > 0)
                    <div class="leo-tabbed-listing__tabs tabs pt-10">
                        <div class="leo-tabs__tab-list flex gap-10">
                            @php $iteration = 1 @endphp
                            @foreach($content->tabs as $tab)
                                <div id="tab-{!! $iteration !!}" class="leo-tabs__tab-list__item !uppercase">
                                    {!! $tab['tab_name'] !!}
                                    <span>{!! $tab['tab_name'] !!}</span>
                                </div>
                                @php $iteration += 1 @endphp
                            @endforeach
                        </div>

                        @php $iterationTwo = 1 @endphp
                        @foreach($content->tabs as $tab)
                            <div id="tab-{!! $iterationTwo !!}-content" class="leo-tabs__tab">
                                <div class="leo-tab-content leo-content text-lightSand/80">
                                    {!! $tab['tab_content'] !!}
                                </div>
                            </div>
                            @php $iterationTwo += 1 @endphp
                        @endforeach
                    </div>
                    @endif
                    
                    @if(!empty($content->price))
                        <div class="leo-content text-lightSand/80 font-body opacity-80 pt-10">
                            {!! $content->price !!}
                        </div>
                    @endif

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
@endif
