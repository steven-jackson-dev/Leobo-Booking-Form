@if(isset($content->hide_section) && $content->hide_section == 'no')
    <section @if(!empty($content->id)) id="{!! $content->id !!}" @endif class="leo-rates-tabs px-7 md:px-10 py-14 md:py-20 lg:py-24 @if(!empty($content->extra_class)){!! $content->extra_class !!}@endif">
        <div class="leo-tabbed-listing__tabs tabs">
            <div class="leo-tabs__tab-list flex !justify-center gap-10">
                @php $iteration = 1 @endphp
                @foreach($content->tabs as $tab)
                    <div
                    id="tab-{!! $iteration !!}"
                    class="leo-tabs__tab-list__item relative"
                    >
                        {!! $tab['tab_name'] !!}
                        <span>{!! $tab['tab_name'] !!}</span>
                    </div>
                    @php $iteration += 1 @endphp
                @endforeach
            </div>
            @php $iterationTwo = 1 @endphp
            @foreach($content->tabs as $tab)
                <div id="tab-{!! $iterationTwo !!}-content" class="leo-tabs__tab">
                   
                    @if(!empty($tab['validity']))
                        <p class="text-center py-14 font-cta italic text-[17px] text-lightSand/80">
                            {!! $tab['validity'] !!}
                        </p>
                    @endif

                    @if(!empty($tab['table']))
                        <div class="leo-rates-table overflow-x-auto">
                            <table class="m-auto min-w-[900px] table-auto">
                                <thead>
                                <tr class="text-left border-l-0">
                                    <th
                                    class="uppercase border align-top pl-5 pb-5 border-l-0 border-t-0 font-light text-[10px] sm:text-[20px]"
                                    >
                                    {!! $tab['table']['heading_row']['heading_column_one'] !!}
                                    </th>
                                    <th
                                    class="uppercase border align-top pl-5 pr-14 pb-5 border-t-0 font-light tracking-wider text-[10px] sm:text-[20px]"
                                    >
                                    {!! $tab['table']['heading_row']['heading_column_two'] !!}
                                    </th>
                                    <th
                                    class="uppercase border align-top pl-5 pr-14 pb-5 border-t-0 font-light text-[10px] sm:text-[20px]"
                                    >
                                    {!! $tab['table']['heading_row']['heading_column_three'] !!}
                                    </th>
                                    <th
                                    class="uppercase border align-top pl-5 pr-14 pb-5 border-t-0 border-r-0 font-light text-[10px]"
                                    >
                                    {!! $tab['table']['heading_row']['heading_column_four'] !!}
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                    @if(is_array($tab['table']['body_row']))
                                        @foreach($tab['table']['body_row'] as $rowItem)
                                            <tr>
                                                <td class="border p-5 border-l-0">
                                                    {!! $rowItem['body_column_one'] !!}
                                                </td>
                                                <td class="border p-5 p-10">
                                                    {!! $rowItem['body_column_two'] !!}
                                                </td>
                                                <td class="border p-5">
                                                    {!! $rowItem['body_column_three'] !!}
                                                </td>
                                                <td class="border p-5 border-t-0 border-r-0">
                                                    {!! $rowItem['body_column_four'] !!}
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    @endif
                    
                    <div class="py-16 flex flex-col items-center">
                        @if(!empty($tab['bottom_bold_text']))
                            <p class="font-cta tracking-[0.05em] uppercase text-center pb-2 text-[20px]">
                                {!! $tab['bottom_bold_text'] !!}
                            </p>
                        @endif
                        @if(!empty($tab['bottom_normal_text']))
                            <p class="font-body font-light text-lightSand/80 text-center text-[17px]">
                                {!! $tab['bottom_normal_text'] !!}
                            </p>
                        @endif
                        @if(!empty($tab['cta']))
                            <div class="leo-btn-wrapper mt-6">
                                <a href="{!! $tab['cta']['url'] !!}" target="{!! $tab['cta']['target'] !!}" class="btn leo-btn-tertiary text-lightSand">{!! $tab['cta']['title'] !!}</a>
                            </div>
                        @endif
                    </div>
                </div>
                @php $iterationTwo += 1 @endphp
            @endforeach
        </div>
    </section>
@endif
