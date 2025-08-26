{{--
  Template Name: Guest Enquiry New
--}}

<section class="form-bg h-[100vh]" style="background-image: url('{!! $guest_enquiry_bg['url'] !!}'); ">
  <header class="leo-header">
    <div class=" bg-maroon left-0 z-[5] w-full h-[105px] px-[30px] md:px-10 flex items-center justify-between">
      <button class="leo-menu-btn uppercase font-heading font-bold text-lightSand tracking-[0.05em] transition-all duration-150 ease-out hover:opacity-70">Menu</button>
      <a href="/" target="_blank" class="leo-main-logo-link h-[41px] w-[162px]">
        <img src="@asset('images/main-logo.svg')" alt="Logo" />
      </a>
    </div>
  </header>

  <section class="leo-menu-modal fixed top-0 left-0 h-[100dvh] w-full z-[5] bg-maroon mobile-menu" style="display:none;">
    <img src="@asset('images/charcoal-bg-high-res.png')" alt="" class="leo-menu-modal-bg-img object-cover absolute top-0 left-0 w-full h-full z-[6]">
    <div class="leo-bg-overlay absolute top-0 left-0 w-full h-full z-[7] bg-maroon/80"></div>
    <div class="leo-top-bar relative z-[8] flex justify-between items-center h-[105px] px-7 md:px-10">
      <img src="@asset('images/icons/close-white.svg')" alt="" class="leo-close-menu-modal hover:cursor-pointer" />
      @if(!empty($header_enquiry_button))
      <a href="{!! $header_enquiry_button['url'] !!}" target="{!! $header_enquiry_button['target'] !!}" class="leo-btn-primary">{!! $header_enquiry_button['title'] !!}</a>
      @endif
    </div>
    <div class="leo-menu-modal-overflow-wrapper overflow-auto h-full">
      <div class="leo-menu-modal__inner relative z-[8] flex flex-col md:flex-row gap-7 pt-7 pb-32 px-7 md:px-10 max-w-[1140px] min-h-[600px] mx-auto">
        <div class="leo-menu-modal__left-col w-full md:w-[47%]">
          {!! wp_nav_menu(['theme_location' => 'primary_navigation', 'container' => false, 'echo' => false]) !!}
        </div>
        <div class="leo-menu-modal__right-col w-full md:w-[53%]">
          <div class="leo-menu-modal-media-wrapper leo-menu-modal-media-wrapper--desktop w-full h-[370px] hidden lg:block">
            @if(!empty($header_videos))
            @php $iteration = 1 @endphp
            @foreach ($header_videos as $item)
            <div id="{!! $item['video_id'] !!}" class="leo-menu-modal-media-wrapper-inner relative h-full w-full @if($iteration > 1) hidden @endif">
              @if(!empty($item['video_url']))
              <video class="leo-menu-modal-video absolute z-[2] top-0 left-0 w-full h-full object-cover object-center" autoplay muted playsinline loop>
                <source src="{{$item['video_url']}}" type="video/mp4">
              </video>
              @endif
              @if(!empty($item['video_poster_image']))
              <img src="{!! $item['video_poster_image']['url'] !!}" alt="{!! $item['video_poster_image']['alt'] !!}" class="leo-menu-modal-video__poster absolute z-[1] top-0 left-0 w-full h-full object-cover object-center">
              @endif
            </div>
            @php $iteration += 1 @endphp
            @endforeach
            @endif
          </div>
          <div class="leo-menu-modal-media-wrapper w-full h-[250px] lg:hidden">
            @if(!empty($header_videos))
            <div id="{!! $header_videos[0]['video_id'] !!}" class="leo-menu-modal-media-wrapper-inner relative h-full w-full">
              @if(!empty($header_videos[0]['video_url']))
              <video class="leo-menu-modal-video absolute z-[2] top-0 left-0 w-full h-full object-cover object-center" autoplay muted playsinline loop>
                <source src="{{$header_videos[0]['video_url']}}" type="video/mp4">
              </video>
              @endif
              @if(!empty($header_videos[0]['video_poster_image']))
              <img src="{!! $header_videos[0]['video_poster_image']['url'] !!}" alt="{!! $header_videos[0]['video_poster_image']['alt'] !!}" class="leo-menu-modal-video__poster absolute z-[1] top-0 left-0 w-full h-full object-cover object-center">
              @endif
            </div>
            @endif
          </div>
          <div class="mt-6">
            {!! wp_nav_menu(['theme_location' => 'secondary_navigation', 'container' => false, 'echo' => false]) !!}
          </div>
          <div class="contact-details font-body font-light text-lightSand tracking-[0.05em]  text-[13px] uppercase text-center mt-7">
            @if(!empty($header_phone_number)) <a class="hover:text-lightSand/80 transition-all duration-150 ease-out" href="tel:{!! $header_phone_number !!}">{!! $header_phone_number !!}</a> @endif | @if(!empty($header_email)) <a class="hover:text-lightSand/80 transition-all duration-150 ease-out" href="mailto:{!! $header_email !!}">{!! $header_email !!}</a> @endif
          </div>
          <div class="leo-socials flex justify-center gap-3 mt-5">
            @if(!empty($header_socials))
            @foreach ($header_socials as $item)
            <a href="{!! $item['link']['url'] !!}" target="{!! $item['link']['target'] !!}" class="hover:opacity-80 transition-all duration-150 ease-out">
              <img src="{!! $item['icon']['url'] !!}" alt="{!! $item['icon']['alt'] !!}" class="leo-social-icon h-[20px] object-contain">
            </a>
            @endforeach
            @endif
          </div>
        </div>
      </div>
    </div>
  </section>

<!-- 
  <section>
    <div class="bg-maroonLight h-[65px] absolute md:top-[105px] z-[4] w-full">
      <div class="custom-progress-indicator" id="custom-progress-indicator">
        <div class="progress-step first-step" data-step="1">
          <div class="progress-label flex !justify-center gap-[10px]">
            <div class="progress-image">
              <img src="@asset('images/icons/date-range-bg.svg')" width="40px" alt="Date Range Icon" class="step-icon">
            </div>
            <span class="step-label dates hidden md:block">DATES</span>
          </div>
          <span class="progress-line"></span>
        </div>
        <div class="progress-step second-step" data-step="2">
          <div class="progress-label flex !justify-center gap-[10px]">
            <div class="progress-image">
              <img src="@asset('images/icons/person-default.svg')" width="40px" alt="Date Range Icon" class="step-icon">
            </div>
            <span class="step-label hidden md:block">GUEST DETAILS</span>
          </div>
          <span class="progress-line"></span>
        </div>
        <div class="progress-step third-step" data-step="3">
          <div class="progress-label flex !justify-center gap-[10px]">
            <div class="progress-image">
              <img src="@asset('images/icons/info-default.svg')" width="40px" alt="Date Range Icon" class="step-icon">
            </div>
            <span class="step-label hidden md:block">INFORMATION</span>
          </div>
        </div>
      </div>
    </div>
  </section> -->

    <section>
      <h1 class="text-2xl font-bold text-lightSand" style='padding:40px; text-align:center; background-color: rgb(34, 20, 24);'><?php the_title(); ?></h1>
      <div class="guest-enquiry">
      <?php echo do_shortcode('[leobo_custom_booking_form]'); ?>
    </div>
  </section>


    
  </section>
</section>




<style>
  /* .custom-progress-indicator {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .progress-step {
    text-align: center;
    flex: 1;
    padding: 10px;
    position: relative;
  }

  

  .progress-step .step-number,
  .progress-step .step-label {
    color: rgba(238, 209, 192, 0.5);
    align-content: center;
  }

  .progress-step .progress-line {
    position: absolute;
    top: 50%;
    right: 0;
    width: 50%;
    height: 2px;
    background-color: rgba(238, 209, 192, 0.5);
    transform: translateX(50%);
    z-index: -1;
  }

  .first-step .progress-line {
    background-color: #EED1C0;
  }

  .progress-step.active .progress-line {
    background-color: #EED1C0;
  }

  .progress-step.completed .step-number,
  span.step-label.dates {
    color: #EED1C0;

  }

  .progress-step.completed .step-label {
    color: #EED1C0;

  }

  .progress-step.completed .progress-line {
    background-color: #EED1C0;

  }

  input:focus,
  textarea:focus,
  select:focus {
    outline: none;
    box-shadow: none;
    border: none;
  } */
</style>

<script>
  jQuery(document).ready(function($) {
    // Update Adults GF Field
    $('#adult-quantity').on('input change', function() {
      var adultQuantity = $(this).val();
      $('#input_1_108').val(adultQuantity);
    });

    $('#gform_1').on('submit', function() {
      var adultQuantity = $('#adult-quantity').val();
      $('#input_1_108').val(adultQuantity);
    });
  });

  jQuery(document).ready(function($) {
    // Update Children GF Field
    $('#children-quantity').on('input change', function() {
      var adultQuantity = $(this).val();
      $('#input_1_107').val(adultQuantity);
    });

    $('#gform_1').on('submit', function() {
      var adultQuantity = $('#children-quantity').val();
      $('#input_1_107').val(adultQuantity);
    });
  });

  jQuery(document).ready(function($) {
    // Update Baby GF Field
    $('#baby-quantity').on('input change', function() {
      var adultQuantity = $(this).val();
      $('#input_1_107').val(adultQuantity);
    });

    $('#gform_1').on('submit', function() {
      var adultQuantity = $('#baby-quantity').val();
      $('#input_1_109').val(adultQuantity);
    });
  });

  document.addEventListener('DOMContentLoaded', function() {
    const progressSteps = document.querySelectorAll('.progress-step');

    function updateProgress(currentStep) {
      progressSteps.forEach((stepElement) => {
        stepElement.classList.remove('active', 'completed');
        const stepNumber = parseInt(stepElement.dataset.step, 10);
        if (stepNumber < currentStep) {
          stepElement.classList.add('completed');
          const progressImage = stepElement.querySelector('.progress-image img');
          if (progressImage) {
            progressImage.src = "@asset('images/icons/check.svg')";
          }
        } else if (stepNumber === currentStep) {
          stepElement.classList.add('active');
        } else {}
      });
    }

    function changeSecondStepLineColor() {
      const firstStep = document.querySelector('.progress-step.first-step');
      const secondStepLine = document.querySelector('.second-step .progress-line');
      const secondStepLabel = document.querySelector('.second-step .step-label');
      const secondStepImage = document.querySelector('.second-step .progress-image img');

      if (firstStep && firstStep.classList.contains('completed') && secondStepLine) {
        secondStepLine.style.backgroundColor = '#EED1C0';
        if (secondStepLabel) {
          secondStepLabel.style.color = '#EED1C0';
        }
        if (secondStepImage) {
          secondStepImage.src = "@asset('images/icons/person-bg.svg')";
        }
      } else {
        if (secondStepLine) {
          secondStepLine.style.backgroundColor = '';
        }
        if (secondStepLabel) {
          secondStepLabel.style.color = '';
        }
        if (secondStepImage) {
          secondStepImage.src = "@asset('images/icons/person-default.svg')";
        }
      }
    }

    function changeThirdStepImage() {
      const secondStep = document.querySelector('.progress-step.second-step.completed');
      const thirdStepImage = document.querySelector('.progress-step.third-step .progress-image img');
      const thirdStepLabel = document.querySelector('.progress-step.third-step .step-label');

      if (secondStep) {
        if (thirdStepImage) {
          thirdStepImage.src = "@asset('images/icons/info-bg.svg')";
        }
        if (thirdStepLabel) {
          thirdStepLabel.style.color = '#EED1C0';
        }
      } else {
        if (thirdStepImage) {
          thirdStepImage.src = "@asset('images/icons/info-default.svg')";
        }
        if (thirdStepLabel) {
          thirdStepLabel.style.color = '';
        }
      }
    }

    // Listen to Gravity Forms' page change event
    jQuery(document).on('gform_page_loaded', function(event, form_id, current_page) {
      if (form_id === 1) {
        updateProgress(current_page);
        changeSecondStepLineColor();
        changeThirdStepImage();
      }
    });

    // Listen for clicks on Next and Previous buttons
    jQuery(document).on('click', '.gform_next_button, .gform_previous_button', function() {
      const targetPageNumber = jQuery('#gform_target_page_number_1').val();
      updateProgress(parseInt(targetPageNumber, 10));
      changeSecondStepLineColor();
      changeThirdStepImage();
    });


  });
</script>