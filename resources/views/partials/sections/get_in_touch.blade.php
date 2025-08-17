@if(isset($content->hide_section) && $content->hide_section == 'no')
    <section class="leo-get-in-touch px-7 md:px-10 py-14 md:py-20 lg:py-24 @if($content->extra_class) {!! $content->extra_class !!} @endif" @if($content->extra_id) id="{!! $content->extra_id !!}" @endif >
        <div class="container mx-auto">
            <div class="flex flex-wrap items-center md:px-[100px]">
                <div class="lg:w-6/12 w-full">
                    <div class="lg:w-9/12">
                        @if(!empty($content->heading))
                            <div class="!text-[48px]">
                                <h2>{!! $content->heading !!}</h3>
                            </div>
                        @endif 
                        @if(!empty($content->description))
                            <div class="w-[80%] leo-content text-lightSand/80 pt-[20px] md:pt-10">
                                <p>{!! $content->description !!}</p>
                            </div>
                        @endif 

                        @if(!empty($content->contact))
                            <div class="leo-content text-lightSand/80 pt-[20px] md:pt-[40px] ">
                                <div class="contact-content">{!! $content->contact !!}</div>
                            </div>
                        @endif 

                        <div class="flex justify-start pt-5">
                            <div class="bg-[#EED1C0]/40 h-[1px] w-3/4"></div>
                        </div>

                        @if(!empty($content->address))
                            <div class="leo-content text-lightSand/80 pt-[20px] md:pt-[30px]">
                                <div class="contact-content">{!! $content->address !!}</div>
                            </div>
                        @endif 

                        @if(!empty($content->button))
                            <div class="pt-[20px] md-[30px]">
                                <a href="{!! $content->button['url'] !!}" class="btn leo-btn-tertiary " target="{{ $content->button['target'] ? $content->button['target'] : '_self' }}">{!! $content->button['title'] !!}</a>
                            </div>
                        @endif
                    </div>   
                </div>
                <div class="lg:w-6/12 w-full pt-[60px]">
                        @if(!empty($content->form_shortcode))
                            <div class="contact-us-form pt-5 md2:pt-10">
                                @php echo do_shortcode($content->form_shortcode);  @endphp
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif 