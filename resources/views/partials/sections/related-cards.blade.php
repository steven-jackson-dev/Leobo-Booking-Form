@php
    $relatedCards = get_field('cards', 'option');
@endphp

@if(isset($content->hide_section) && $content->hide_section == 'no')
    <section @if(!empty($content->id)) id="{!! $content->id !!}" @endif class="leo-related-cards overflow-hidden relative px-7 md:px-0 my-16 md:my-28 lg:my-36 flex flex-col-reverse gap-7 md:gap-[0px] md:flex-row justify-between items-center w-full @if(!empty($content->extra_class)){!! $content->extra_class !!}@endif">
        

        @if($content->card_one)
            @foreach($relatedCards as $card)
                    @if($content->card_one === $card['card_type'])
                        <div class="leo-left-img relative md:static flex items-end justify-center md:justify-end h-[95vw] md:h-[42vw] w-full md:w-[30vw] rounded-[5px]">
                            @if(!empty($card['link']))
                                <a href="{!! $card['link']['url'] !!}" class="leo-left-img-animated image-left absolute top-0 left-0 flex items-end justify-center md:justify-end h-[95vw] md:h-[42vw] w-full md:w-[44vw] rounded-[5px]">
                                    @if(!empty($card['image']))
                                        <img src="{!! $card['image']['url'] !!}" loading="lazy" class="absolute z-[1] top-0 left-0 w-full h-full object-cover object-center rounded-[5px]" width="430" height="619" loading="lazy" alt="{!! $card['image']['url'] !!}">
                                    @endif
                                    <div class="leo-overlay absolute z-[2] !h-[40%] w-full bottom-0 left-0 bg-gradient-to-b from-[transparent] to-[#000]/80 rounded-[5px]"></div>
                                    <p class="relative z-[3] font-light font-body p-7 text-[#EED1C0] text-[22px] tracking-[0.05em]">{!! $card['link']['title'] !!}</p>
                                </a>
                            @endif
                        </div>
                    @endif
            @endforeach
        @endif

        <div class="text-center order-1 md:order-none relative z-[7]">
            <div class="scroll_fadeani w-[max-content] flex flex-col items-center gap-5 mx-7 md:mx-10 lg:mx-14 mb-5 md:my-0 mt-2">
                <div class="flex justify-center pb-4">
                    <div class="outline outline-lightSand aspect-square rounded-[.5px] bg-transparent border-lightSand border-[5px] w-5"></div>
                </div>
                <h2 class="!text-[24px] md:!text-[28px] lg:!text-[38px] text-lightSand">
                    Our world.
                </h2>
                <h2 class="!text-[24px] md:!text-[28px] lg:!text-[38px] text-lightSand">
                    Your playground.
                </h2>
            </div>
        </div>

        @if($content->card_two)
            @foreach($relatedCards as $card)
                    @if($content->card_two === $card['card_type'])
                        <div class="leo-right-img relative md:static flex items-end justify-center md:justify-start h-[95vw] md:h-[42vw] w-full md:w-[30vw] rounded-[5px]">
                            @if(!empty($card['link']))
                                <a href="{!! $card['link']['url'] !!}" class="leo-right-img-animated image-right absolute top-0 right-0 flex items-end justify-center md:justify-start h-[95vw] md:h-[42vw] w-full md:w-[44vw] rounded-[5px]">
                                    @if(!empty($card['image']))
                                        <img src="{!! $card['image']['url'] !!}" loading="lazy" class="absolute z-[1] top-0 left-0 w-full h-full object-cover object-center rounded-[5px]" width="430" height="619" loading="lazy" alt="{!! $card['image']['alt'] !!}">
                                    @endif
                                    <div class="leo-overlay absolute z-[2] !h-[40%] w-full bottom-0 left-0 bg-gradient-to-b from-[transparent] to-[#000]/80 rounded-[5px]"></div>
                                    <p class="relative z-[3] font-light font-body p-7 text-[#EED1C0] text-[22px] tracking-[0.05em]">{!! $card['link']['title'] !!}</p>
                                </a>
                            @endif
                        </div>
                    @endif
            @endforeach
        @endif

    </section>
@endif