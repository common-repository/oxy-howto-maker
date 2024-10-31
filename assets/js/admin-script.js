jQuery(document).ready(function ($) {
  $(window).on('keydown', function (event) {
    if (event.keyCode === 13) {
      event.preventDefault();
      return false;
    }
  });

  const oxy_howto_is_rtl = !!$('html[dir="rtl"]').length;

  $(document).on('change', '[name="oxy_howto_side_status"]', function () {
    const howto_status = $(this).is(':checked');
    const $oxy_howto_status = $('[name="oxy_howto_status"]');
    $oxy_howto_status.prop('checked', howto_status);
    $oxy_howto_status.trigger('change');
    if (howto_status) {
      $('.oxy-howto-maker-side-make-it').removeAttr('disabled');
    } else {
      $('.oxy-howto-maker-side-make-it').attr('disabled', 'disabled');
    }
  });

  $(document).on('change', '[name="oxy_howto_status"]', function () {
    const howto_status = $(this).is(':checked');
    $('[name="oxy_howto_side_status"]').prop('checked', howto_status);
    if (howto_status) {
      $('.oxy-howto-maker-side-make-it').removeAttr('disabled');
    } else {
      $('.oxy-howto-maker-side-make-it').attr('disabled', 'disabled');
    }
  });

  $(document).on('click', '.oxy-howto-maker-side-make-it', function () {
    $('.oxy-howto-maker-make-it').first().trigger('click');
  });

  setTimeout(function () {
    const $initTinymce = $('.init-tinymce');
    if ($initTinymce.length) {
      $.each($initTinymce, function (index, tiny) {
        const id = $(tiny).attr('id');
        setTimeout(function () {
          tinyMCE.init({
            plugins: 'link lists',
            toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | outdent indent | link | bullist',
            directionality: (oxy_howto_is_rtl ? 'rtl' : 'ltr'),
            setup: function (ed) {
              ed.on('BeforeSetcontent', function (o) {
                if (o.initial) {
                  o.content = o.content.replace(/\r?\n/gm, '');
                }
              });
            }
          });
          tinyMCE.execCommand('mceAddEditor', false, id);
          tinyMCE.get(id).on('input', function (el) {
            $('#' + id).val(el.target.innerHTML);
          });
        }, 2500);
      });
    }
  }, 1000);

  // Step
  $(document).on('click', '.oxy-howto-maker-add-step-x', function () {
    const $oxy_howto_maker_add_step = $('.oxy-howto-maker-add-step');
    if (oxy_howto_trans.config.step > $oxy_howto_maker_add_step.length) {
      $('.oxy-howto-maker-add-step > div').hide();
      $('.oxy-howto-maker-add-step > .oxy-howto-step-header > i').addClass('dashicons-arrow-down-alt2').removeClass('dashicons-arrow-up-alt2');
      const cloned_el = $oxy_howto_maker_add_step.last().clone();
      $(cloned_el).find('.mce-tinymce').remove();
      $(this).parent().parent().after(cloned_el);
      $(cloned_el).find(' > div').slideDown('fast');
      $(cloned_el).find(' > .oxy-howto-step-header > i').addClass('dashicons-arrow-up-alt2').removeClass('dashicons-arrow-down-alt2');
      $(cloned_el).find('.oxy-howto-maker-add-stepdivtext').not(':first-of-type').remove();
      $(cloned_el).find('.oxy-howto-maker-add-stepdivtip').not(':last-of-type').remove();
      $(cloned_el).find('.oxy-howto-maker-add-stepdivtip, .oxy-howto-maker-delete-step-image').addClass('oxy-d-none');
      $(cloned_el).find('input, textarea').val('');
      $(cloned_el).find('img').attr('src', '');

      const scrollTo = $(cloned_el).find(' > .oxy-howto-step-header');
      $('html, body').animate({
        scrollTop: $(scrollTo).offset().top - 50
      }, 500);

      reorder_steps();
    }
  });

  // Tool & Supply
  let supply_processing = false;
  $(document).on('click', '#oxy-howto-maker-add-tool, #oxy-howto-maker-add-supply', function () {
    if (supply_processing === false) {
      supply_processing = true;
      const id = $(this).attr('id');
      const mx_items = id === 'oxy-howto-maker-add-tool' ? oxy_howto_trans.config.tool : oxy_howto_trans.config.supply;
      const section = '.' + id;
      const items_count = $(section).length - 1;

      if (mx_items > items_count) {
        const last_el = $(section).last();
        const cloned_el = $(last_el).clone().css('display', 'none');

        const oxy_number = $(cloned_el).find('.oxy-number').last().text();
        const last_oxy_number = parseInt(oxy_number.replace(/\D+/, ''));
        const oxy_text = oxy_number.replace(/\d+/, '');

        $(cloned_el).find('.oxy-number').last().text(oxy_text + ' ' + (last_oxy_number + 1));
        $(cloned_el).find('.oxy-d-none.oxy-d-block-later').removeClass('oxy-d-none').addClass('oxy-d-block');
        $(cloned_el).find('.oxy-d-none').removeClass('oxy-d-none');

        let name = $(cloned_el).find('input[name$="[name]"]').attr('name');
        name = name.replace(/\d/g, last_oxy_number + 1);
        $(cloned_el).find('input[name$="[name]"]').attr('name', name);

        let url = $(cloned_el).find('input[name$="[url]"]').attr('name');
        url = url.replace(/\d/g, last_oxy_number + 1);
        $(cloned_el).find('input[name$="[url]"]').attr('name', url);

        if (oxy_howto_trans.config.nofollow === 'on') {
          let nofollow = $(cloned_el).find('input[name$="[nofollow]"]').attr('name');
          nofollow = nofollow.replace(/\d/g, last_oxy_number + 1);
          $(cloned_el).find('input[name$="[nofollow]"]').attr('name', nofollow);
        }

        $(last_el).after(cloned_el);
        if (oxy_howto_trans.config.nofollow === 'on') {
          $(section).last().find('input[type="checkbox"]').prop('checked', false);
        }
        $(section).last().slideDown('fast', function () {
          supply_processing = false;
        }).find('input').val('');
      }
    }
  });

  // Direction & Tip
  $(document).on('click', 'button[class*="oxy-howto-maker-add-steptext"], button[class*="oxy-howto-maker-add-steptip"]', function () {
    const selectedClass = $(this).attr('class').indexOf('oxy-howto-maker-add-steptext');
    const mx_items = selectedClass > -1 ? oxy_howto_trans.config.step_direction : oxy_howto_trans.config.step_tip;
    const grand_parent = $(this).parent().parent();
    let items = $(grand_parent).find('.oxy-howto-maker-add-stepdivtip');
    let items_count = $(items).length - 1;
    if (selectedClass > -1) {
      items = $(grand_parent).find('.oxy-howto-maker-add-stepdivtext');
      items_count = $(items).length;
    }
    if (mx_items > items_count) {
      const current_el = $(items)[0];
      const cloned_el = $(current_el).clone().css('display', 'none');
      $(cloned_el).find('.mce-tinymce').remove();
      $(this).parent().after(cloned_el);
      $(cloned_el).find('.oxy-d-none').removeClass('oxy-d-none');
      $(cloned_el).find('.original').removeClass('original');
      $(cloned_el).find('textarea').show();
      $(cloned_el).removeClass('oxy-d-none');
      $(cloned_el).slideDown('fast').find('input, textarea').val('');

      reorder_directions($(cloned_el).parent(), $(cloned_el));
    }
  });

  // On/Off Howto
  $(document).on('change', '#oxy-howto-maker-switcher input', function () {
    const $oxy_howto_maker_wrap = $('#oxy-howto-maker-wrap');
    $(this).is(':checked') ? $oxy_howto_maker_wrap.slideDown('fast') : $oxy_howto_maker_wrap.slideUp('fast');
  });

  $(document).on('click', '.oxy-howto-maker-make-it', function () {
    const errors = oxy_validate_howto_maker();

    if (errors.length) {
      resetPublish();
      alert(errors.join('\n'));
    } else {
      $('#content-tmce').trigger('click');

      let content = '<div class="oxy-howto-maker-brief-wrap">';

      // Difficulty
      const difficulty = parseInt($('[name="oxy_data[difficulty]"]').val());
      if (difficulty >= 1 && difficulty <= 3) {
        content += '<div class="oxy-howto-maker-difficulty-wrap" data-difficulty="' + difficulty + '">' +
          '<label class="oxy-howto-difficulty-label">' +
          '<i class="dashicons dashicons-dashboard"></i> ' +
          oxy_howto_trans.site_trans.difficulty +
          '</label>' +
          '<div class="oxy-howto-difficulty-div-wrap">';

        for (let d = 1; d <= 3; d++) {
          if (d <= difficulty) {
            content += '<div class="oxy-howto-difficulty-div active"></div>';
          } else {
            content += '<div class="oxy-howto-difficulty-div"></div>';
          }
        }

        content += '</div><div class="clearfix"></div></div>';
      }

      const $oxy_howto_maker_add_step = $('.oxy-howto-maker-add-step');
      const step_count = $oxy_howto_maker_add_step.length;
      content += '<div class="oxy-howto-maker-step-count-wrap" data-step-count="' + step_count + '">' +
        '<label class="oxy-howto-step-count-label">' +
        '<i class="dashicons dashicons-editor-ul"></i> ' +
        oxy_howto_trans.site_trans.step_count +
        '</label>' +
        '<p class="oxy-howto-step-count-p">' + step_count + '</p>' +
        '<div class="clearfix"></div>' +
        '</div>';

      // Estimated Cost
      const cost = $('[name="oxy_data[estimatedCost][value]"]').val();
      if (cost > 0) {
        const $oxy_data_estimated_cost_currency = $('[name="oxy_data[estimatedCost][currency]"]');
        const currency = $oxy_data_estimated_cost_currency.val();
        const currency_symbol = $oxy_data_estimated_cost_currency.find(':selected').attr('data-symbol');
        let cost_formatted = currency_symbol + cost;
        if (currency === 'IRR' || currency === 'IRT') {
          cost_formatted = cost + ' ' + currency_symbol;
        }
        content += '<div class="oxy-howto-maker-estimated-cost">' +
          '<label class="oxy-howto-estimated-cost-label">' +
          '<i class="dashicons dashicons-money-alt"></i> ' +
          oxy_howto_trans.site_trans.estimated_cost +
          '</label>' +
          '<p class="oxy-howto-estimated-cost-p">' + cost_formatted + '</p>' +
          '<div class="clearfix"></div>' +
          '</div>';
      }

      // Total Time
      const day = parseInt($('#oxy-howto-maker-total-time [name="oxy_data[day]"]').val());
      const hour = parseInt($('#oxy-howto-maker-total-time [name="oxy_data[hour]"]').val());
      const minute = parseInt($('#oxy-howto-maker-total-time [name="oxy_data[minute]"]').val());
      let total_time = '';
      if (day > 0) {
        total_time += day + ' ' + oxy_howto_trans.site_trans.day + ' ' + oxy_howto_trans.site_trans.ampersand + ' ';
      }
      if (hour > 0) {
        total_time += hour + ' ' + oxy_howto_trans.site_trans.hour + ' ' + oxy_howto_trans.site_trans.ampersand + ' ';
      }
      if (minute > 0) {
        total_time += minute + ' ' + oxy_howto_trans.site_trans.minute + ' ' + oxy_howto_trans.site_trans.ampersand + ' ';
      }
      const replace = `\\s${oxy_howto_trans.site_trans.ampersand}\\s$`;
      const re = new RegExp(replace);
      total_time = total_time.replace(re, '');
      content += '<div class="oxy-howto-maker-total-time">' +
        '<label class="oxy-howto-total-time-label">' +
        '<i class="dashicons dashicons-clock"></i> ' +
        oxy_howto_trans.site_trans.total_time +
        '</label>' +
        '<p class="oxy-howto-total-time-paragraph">' + total_time + '</p>' +
        '<div class="clearfix"></div>' +
        '</div>';

      // oxy-howto-maker-brief-wrap ends
      content += '</div>';

      // Description
      content += '<div class="oxy-howto-maker-description">' + tinyMCE.get('oxy_data-description').getContent() + '</div>';

      // Supply
      let supplies = '';
      let supply_count = 0;
      $.each($('.oxy-howto-maker-add-supply'), function (index, el) {
        const supply_name = $(el).find('[name^="oxy_data[supply]"][name$="[name]"]').val().trim();
        if (supply_name !== '') {
          supply_count++;
          const supply_url = $(el).find('[name^="oxy_data[supply]"][name$="[url]"]').val().trim();
          if (supply_url !== '' && supply_url !== '#') {
            let rel = 'noopener';
            if ($(el).find('[name^="oxy_data[supply]"][name$="[nofollow]"]').is(':checked')) {
              rel += ' nofollow';
            }
            supplies += '<li class="oxy-howto-supplies-li">' +
              '<p class="oxy-howto-supplies-h2">' +
              '<a class="oxy-howto-supplies-a" href="' + supply_url + '" target="_blank" rel="' + rel + '">' + supply_name + '</a>' +
              '</p>' +
              '</li>';
          } else {
            supplies += '<li class="oxy-howto-supplies-li">' +
              '<p class="oxy-howto-supplies-h2">' + supply_name + '</p>' +
              '</li>';
          }
        }
      });

      if (supplies !== '') {
        supplies = (supply_count > 1 ? '<ul>' : '<ul class="oxy-howto-list-unstyled">') + supplies + '</ul>';

        let supply_title = '';
        const supply_title_value = $('#oxy_howto_maker_metabox .inside [name="oxy_data[supply][title]"]').val();
        if (supply_title_value === 'supply') {
          supply_title = supply_count > 1 ? oxy_howto_trans.site_trans.supplies : oxy_howto_trans.site_trans.supply;
        } else if (supply_title_value === 'material') {
          supply_title = supply_count > 1 ? oxy_howto_trans.site_trans.materials : oxy_howto_trans.site_trans.material;
        } else if (supply_title_value === 'necessary_item') {
          supply_title = supply_count > 1 ? oxy_howto_trans.site_trans.necessary_items : oxy_howto_trans.site_trans.necessary_item;
        }

        content +=
          '<div class="oxy-howto-maker-supplies">' +
          '<h2 class="oxy-howto-supplies-label">' + supply_title + '</h2>' + supplies +
          '</div>';
      }

      // Tool
      let tools = '';
      let tool_count = 0;
      $.each($('.oxy-howto-maker-add-tool'), function (index, el) {
        const tool_name = $(el).find('[name^="oxy_data[tool]"][name$="[name]"]').val().trim();
        if (tool_name !== '') {
          tool_count++;
          const tool_url = $(el).find('[name^="oxy_data[tool]"][name$="[url]"]').val().trim();
          if (tool_url !== '' && tool_url !== '#') {
            let rel = 'noopener';
            if ($(el).find('[name^="oxy_data[tool]"][name$="[nofollow]"]').is(':checked')) {
              rel += ' nofollow';
            }
            tools += '<li class="oxy-howto-tools-li">' +
              '<a class="oxy-howto-tools-a" href="' + tool_url + '" target="_blank" rel="' + rel + '">' + tool_name + '</a>' +
              '</li>';
          } else {
            tools += '<li class="oxy-howto-tools-li">' +
              tool_name +
              '</li>';
          }
        }
      });
      if (tools !== '') {
        tools = (tool_count > 1 ? '<ul>' : '<ul class="oxy-howto-list-unstyled">') + tools + '</ul>';

        content += '<div class="oxy-howto-maker-tools">' +
          '<h2 class="oxy-howto-tools-label">' + (tool_count > 1 ? oxy_howto_trans.site_trans.tools : oxy_howto_trans.site_trans.tool) + '</h2>' +
          tools +
          '</div>';
      }

      // Step
      let steps = '';
      let step = 1;
      $.each($oxy_howto_maker_add_step, function (index, el) {
        const step_name = $(el).find('> div').find('.oxy-data-step-name').val();
        if (step_name !== '') {
          // Step Name
          steps += '<h2 id="step' + step + '" class="oxy-howto-step-head">' +
            oxy_howto_trans.site_trans.step + ' ' + step + ': ' + step_name +
            '</h2>';

          // Step Image
          const img = $(el).find('[class*="oxy-howto-maker-step-image-input"]');
          const src = $(img).val();
          const image_id = $(img).attr('data-image-id');
          const image_width = $(img).attr('data-image-width');
          const image_height = $(img).attr('data-image-height');
          let image_alt = $(img).attr('data-image-alt');
          const image_srcset = $(img).attr('data-image-srcset');
          let image_caption = $(img).attr('data-image-caption').trim();
          if (image_caption != '') {
            const caption_id = `caption-attachment-${image_id}`;
            image_caption = '<p id="' + caption_id + '" class="wp-caption-text">' + image_caption + '</p>';
          }
          if (image_alt === '') {
            image_alt = step_name;
          }

          steps += '<div class="oxy-howto-step-image">' +
            '<img class="size-full wp-image-' + image_id + ' oxy-howto-step-image-img"' +
            ' src="' + src + '"' +
            ' data-src="' + src + '"' +
            ' srcset="' + image_srcset + '"' +
            ' data-srcset="' + image_srcset + '"' +
            ' alt="' + image_alt + '"' +
            ' sizes="(max-width: ' + image_width + 'px) 100vw, ' + image_width + 'px"' +
            ' data-sizes="(max-width: ' + image_width + 'px) 100vw, ' + image_width + 'px"' +
            ' width="' + image_width + '"' +
            ' height="' + image_height + '"' +
            ' loading="lazy">' +
            image_caption +
          '</div>';

          // Step Direction & Tip
          const stepdiv = $(el).find('> div > .oxy-howto-maker-add-stepdivtext, > div > .oxy-howto-maker-add-stepdivtip');
          $.each(stepdiv, function (index, el2) {
            const id = $(el2).find('textarea').attr('id');
            const tiny = tinyMCE.get(id);
            if (tiny) {
              let value = tiny.getContent().trim();
              let dt_class = 'oxy-howto-tip-head';
              let text_before = '';
              if ($(el2).hasClass('oxy-howto-maker-add-stepdivtext')) {
                dt_class = 'oxy-howto-direction-head';
              } else {
                text_before = oxy_howto_trans.site_trans.tip + ': ';
              }
              if (value !== '') {
                value = '<br>' + value;
                steps += '<div class="' + dt_class + '">' + text_before + value + '</div>';
              }
            }
          });

          step++;
        }
      });
      content += '<div class="oxy-howto-maker-steps">' + steps + '</div>';
      content = content.replace(/<h2 .*?class="(.*?vc_custom_heading.*?)">(.*?)<\/h2>/, '');
      content = content.trim();

      if ($('body').hasClass('block-editor-page')) {
        // Gutenberg
        const editedContent = wp.data.select('core/editor').getEditedPostContent();
        const editedContentParsed = wp.blocks.parse(editedContent);
        let found = -1;
        $.each(editedContentParsed, function (index, entry) {
          const element = $.parseHTML(entry.attributes.content);
          if (element[0] && element[0].id === 'oxy-howto-maker-data-made') {
            found = index;
          }
        });

        content = '<div id="oxy-howto-maker-data-made">' + content + '</div>';
        const block = wp.blocks.rawHandler({HTML: content});

        if (found > -1) {
          editedContentParsed[found].attributes.content = block[0].attributes.content;
          wp.data.dispatch('core/editor').resetEditorBlocks(editedContentParsed);
        } else {
          wp.data.dispatch('core/editor').insertBlocks(block);
        }

        alert(oxy_howto_trans.user_trans.successfully_generated);
        $('html, body, .interface-interface-skeleton__content').animate({scrollTop: 0}, 'slow');

      } else {
        // Non Gutenberg
        const tiny_mce = $('#content_ifr').contents().find('#tinymce');
        if ($(tiny_mce).find('#oxy-howto-maker-data-made').length) {
          $(tiny_mce).find('#oxy-howto-maker-data-made').html(content);
        } else {
          content = '<div id="oxy-howto-maker-data-made">' + content + '</div>';
          $(tiny_mce).append(content);
        }

        const tiny_content = tinyMCE.get('content');
        let temp_content = tiny_content.getContent();
        temp_content = temp_content.replace(/<p>\s.*?<\/p><div\s+id="oxy-howto-maker-data-made"/, '<div id=\"oxy-howto-maker-data-made"');
        tiny_content.setContent(temp_content);

        alert(oxy_howto_trans.user_trans.successfully_generated);
        $('html, body').animate({scrollTop: 0}, 'slow');
      }

      const $form_post = $('form#post');
      if ($form_post.hasClass('wait-to-generate')) {
        $form_post.removeClass('wait-to-generate').addClass('generated').trigger('submit');
      }
    }
  });

  $(document).on('submit', 'form#post', function (e) {
    if ($('#oxy-howto-maker-switcher input').is(':checked')) {
      if ($(this).hasClass('generated')) {
        $(this).removeClass('generated');
      } else {
        const errors = oxy_validate_howto_maker();

        // Post Title
        if ($('[name="post_title"]').val().trim() === '') {
          errors.push(oxy_howto_trans.user_trans.title_required);
        }

        // Featured Image
        const featured_image = $('[name="_thumbnail_id"]').val();
        if (featured_image.trim() === '' || featured_image == '-1') {
          errors.push(oxy_howto_trans.user_trans.featured_image_required);
        }

        if (errors.length) {
          e.preventDefault();
          resetPublish();
          alert(errors.join('\n'));
          return;
        }

        // Post Content
        if ($('[name="content"]').val().trim() === '') {
          e.preventDefault();
          $('form#post').addClass('wait-to-generate');
          $('.oxy-howto-maker-make-it').first().trigger('click');
        }
      }
    }
  });

  $.each($('#oxy-howto-maker-step-section > .oxy-howto-maker-add-step > div'), function () {
    const id = $(this).attr('id');
    const step = id.replace(/\D/g, '');
    $('.oxy-scroll-to').append('<option value="#' + id + '">' + step + '</option>');
  });

  $(document).on('click', '.fold-all', function () {
    const $oxy_howto_maker_toggler = $('.oxy-howto-maker-toggler');
    const last = $oxy_howto_maker_toggler.length - 1;
    $.each($oxy_howto_maker_toggler, function (index, el) {
      const data_target = $(el).attr('data-target');
      $(data_target).slideUp('fast');
      if (last === index) {
        $('.fold-all').hide();
        $('.unfold-all').show();
      }
    });
  });

  $(document).on('click', '.unfold-all', function () {
    const $oxy_howto_maker_toggler = $('.oxy-howto-maker-toggler');
    const last = $oxy_howto_maker_toggler.length - 1;
    $.each($oxy_howto_maker_toggler, function (index, el) {
      const data_target = $(el).attr('data-target');
      $(data_target).slideDown('fast');
      if (last === index) {
        $('.fold-all').show();
        $('.unfold-all').hide();
      }
    });
  });

  $(document).on('change', '.oxy-scroll-to', function () {
    const scrollTo = $(this).val();
    $(scrollTo).show();
    $('#oxy-howto-maker-step-section').show();
    setTimeout(function () {
      $('html, body').animate({
        scrollTop: $(scrollTo).offset().top - 100
      }, 500);
    }, 100);
  });

  $(document).on('click', '.delete-direction, .delete-tip', function () {
    const confirmed = confirm(oxy_howto_trans.user_trans.r_u_sure);
    if (confirmed) {
      const grand_parent_parent = $(this).parent().parent().parent();
      $(this).parent().parent().remove();
      reorder_directions(grand_parent_parent);
    }
  });
  $(document).on('click', '.delete-supply, .delete-tool', function () {
    const confirmed = confirm(oxy_howto_trans.user_trans.r_u_sure);
    if (confirmed) {
      const st = $(this).hasClass('delete-tool') ? 'tool' : 'supply';

      const grand_parent = $(this).parent().parent();
      $(this).parent().remove();

      $.each($(grand_parent).find('.oxy-howto-maker-add-' + st), function (index, el) {
        const oxy_number = $(el).find('.oxy-number').text();
        const oxy_text = oxy_number.replace(/\d+/, '');
        $(el).find('.oxy-number').text(oxy_text + ' ' + index);
      });
    }
  });
  $(document).on('click', '.delete-step', function () {
    const confirmed = confirm(oxy_howto_trans.user_trans.r_u_sure);
    if (confirmed) {
      const step = $(this).parent().parent().prev();
      $(this).parent().parent().remove();
      reorder_steps_delete(step);

      $('.oxy-scroll-to').find('option').not(':first').remove();
      $.each($('#oxy-howto-maker-step-section > .oxy-howto-maker-add-step > div'), function () {
        const id = $(this).attr('id');
        const step = id.replace(/\D/g, '');
        $('.oxy-scroll-to').append('<option value="#' + id + '">' + step + '</option>');
      });
    }
  });

  let meta_image_frame;
  $(document).on('click', 'button[class^="oxy-howto-maker-step-image"]', function () {
    let image_index = parseInt($(this).attr('class').replace(/\D+/g, ''));

    // If the frame already exists, just open it.
    if (meta_image_frame) {
      meta_image_frame.image_index = image_index;
      meta_image_frame.open();
      return;
    }

    // Sets up the media library frame
    meta_image_frame = wp.media.frames.meta_image_frame = wp.media(oxy_howto_trans.meta_image_frame_options);

    // Runs when an image is selected.
    meta_image_frame.on('select', function () {
      if (meta_image_frame.image_index) {
        image_index = meta_image_frame.image_index;
      }
      // Grabs the attachment selection and creates a JSON representation of the model.
      const media_attachment = meta_image_frame.state().get('selection').first().toJSON();

      const sizes = media_attachment.sizes;
      let data_image_srcset = sizes.full.url + ' ' + sizes.full.width + 'w, ';
      const existing_urls = [{[sizes.full.url]: 1}];

      if (sizes.large && !existing_urls[0][sizes.large.url]) {
        data_image_srcset += sizes.large.url + ' ' + sizes.large.width + 'w, ';
        existing_urls[0][sizes.large.url] = 1;
      }

      if (sizes.medium && !existing_urls[0][sizes.medium.url]) {
        data_image_srcset += sizes.medium.url + ' ' + sizes.medium.width + 'w, ';
        existing_urls[0][sizes.medium.url] = 1;
      }

      if (sizes.thumbnail && !existing_urls[0][sizes.thumbnail.url]) {
        data_image_srcset += sizes.thumbnail.url + ' ' + sizes.thumbnail.width + 'w';
        existing_urls[0][sizes.thumbnail.url] = 1;
      }

      data_image_srcset = data_image_srcset.replace(/, $/, '');

      // Sends the attachment URL to our custom image input field.
      $('.oxy-howto-maker-step-image-input' + image_index)
        .val(media_attachment.url)
        .attr({
          'data-image-id': media_attachment.id,
          'data-image-width': media_attachment.sizes.full.width,
          'data-image-height': media_attachment.sizes.full.height,
          'data-image-alt': media_attachment.alt,
          'data-image-srcset': data_image_srcset,
          'data-image-caption': media_attachment.caption
        });
      const $oxy_howto_maker_step_image_img = $('.oxy-howto-maker-step-image-img' + image_index);
      $oxy_howto_maker_step_image_img.attr('src', media_attachment.sizes.full.url);
      $oxy_howto_maker_step_image_img.parent().find('.oxy-howto-maker-delete-step-image').removeClass('oxy-d-none');
    });

    // Opens the media library frame.
    meta_image_frame.open();
  });

  $(document).on('click', '.oxy-howto-maker-delete-step-image', function () {
    const confirmed = confirm(oxy_howto_trans.user_trans.r_u_sure);
    if (confirmed) {
      $(this).parent().find('img').attr('src', '');
      $(this).parent().find('input[type="hidden"]')
        .val('')
        .removeAttr('data-image-id')
        .removeAttr('data-image-width')
        .removeAttr('data-image-height')
        .removeAttr('data-image-alt')
        .removeAttr('data-image-srcset')
        .removeAttr('data-image-caption');
      $(this).addClass('oxy-d-none');
    }
  });

  $('#oxy-howto-maker-total-time input[type="number"]').on('input keydown keyup change paste', function (e) {
    const char = e.which ?? e.keyCode;
    return (char === 8 || char === 9 || char === 37 || char === 39 || char === 46) ||
      (char >= 48 && char <= 57) || (char >= 96 && char <= 105);
  });

  $(document).on('click', '.oxy-howto-maker-toggler', function () {
    const target = $(this).attr('data-target');
    if ($(target).is(':visible')) {
      $(target).slideUp('fast');
      $(this).find('i').removeClass('dashicons-arrow-up-alt2').addClass('dashicons-arrow-down-alt2');
    } else {
      $(target).slideDown('fast');
      $(this).find('i').removeClass('dashicons-arrow-down-alt2').addClass('dashicons-arrow-up-alt2');
    }
  });

  $(document).on('change', '#oxy_howto_maker_metabox .inside [name="oxy_data[supply][title]"]', function () {
    const selected_option = $(this).find('option:selected');
    const selected_title = selected_option.data('title');
    const add_title = selected_option.data('add');
    $('#oxy-howto-maker-add-supply').text(add_title);
    $.each($('.oxy-howto-maker-add-supply'), function (index, el) {
      const oxy_number = $(el).find('.oxy-number');
      const title = oxy_number.text().replace(/\D+/, selected_title + ' ');
      oxy_number.text(title);
    });
  });
});

function resetPublish() {
  jQuery('#publishing-action #publish').removeClass('disabled');
  jQuery('#publishing-action .spinner').removeClass('is-active');
  jQuery('[name="hidden_post_status"]').val('publish');
}

function oxy_replace_occurrence(string, regex, n, replace) {
  string = string.trim();
  if (string !== '') {
    let i = 0;
    return string.replace(regex, function (match) {
      i++;
      return i === n ? replace : match;
    });
  }

  return string;
}

function oxy_validate_howto_maker() {
  const $ = jQuery;

  const errors = [];

  // Description
  const $oxy_date_description = $('[name="oxy_data[description]"]');
  let description = $oxy_date_description.val().trim();
  if ($('#wp-content-wrap').hasClass('tmce-active')) {
    description = tinyMCE.get($oxy_date_description.attr('id')).getContent().trim();
  }

  if (description === '' || description === '<p><br></p>') {
    errors.push(oxy_howto_trans.user_trans.description_required);
  }

  // Estimated Cost
  let estimated_cost_value = $('[name="oxy_data[estimatedCost][value]"]').val();
  estimated_cost_value = estimated_cost_value.replace(/,/g, '');
  const valid_estimated_cost_value = /^\d+(\.\d+)?$/.test(estimated_cost_value);
  if (!valid_estimated_cost_value) {
    errors.push(oxy_howto_trans.user_trans.estimated_cost_validity);
  }

  // Price Currency
  const price_currency = $('[name="oxy_data[estimatedCost][currency]"]').val();
  if (price_currency === '' && estimated_cost_value != 0) {
    errors.push(oxy_howto_trans.user_trans.price_currency_validity);
  }

  // Supply
  $.each($('.oxy-howto-maker-add-supply:not(:first-of-type)'), function (index, el) {
    const name = $(el).find('[name^="oxy_data[supply]"][name$="[name]"]').val();
    if (name.trim() === '') {
      const supply_name_validity = oxy_howto_trans.user_trans.supply_name_validity.replace(/%s/, (index + 1));
      errors.push(supply_name_validity);
    }

    const value = $(el).find('[name^="oxy_data[supply]"][name$="[url]"]').val();
    if (value !== '') {
      const patt = /[-a-zA-Z\d@:%._+~#=]{1,256}\.[a-zA-Z\d()]{1,6}\b([-a-zA-Z\d()@:%_+.~#?&/=]*)/g;
      const valid_supply_url = patt.test(value);
      if (valid_supply_url === false) {
        const supply_url_validity = oxy_howto_trans.user_trans.supply_url_validity.replace(/%s/, (index + 1));
        errors.push(supply_url_validity);
      }
    }
  });

  // Tool
  $.each($('.oxy-howto-maker-add-tool:not(:first-of-type)'), function (index, el) {
    const name = $(el).find('[name^="oxy_data[tool]"][name$="[name]"]').val();
    if (name.trim() === '') {
      const tool_name_validity = oxy_howto_trans.user_trans.tool_name_validity.replace(/%s/, (index + 1));
      errors.push(tool_name_validity);
    }

    const value = $(el).find('[name^="oxy_data[tool]"][name$="[url]"]').val();
    if (value !== '') {
      const patt = /[-a-zA-Z\d@:%._+~#=]{1,256}\.[a-zA-Z\d()]{1,6}\b([-a-zA-Z\d()@:%_+.~#?&/=]*)/g;
      const valid_tool_url = patt.test(value);
      if (valid_tool_url === false) {
        const tool_url_validity = oxy_howto_trans.user_trans.tool_url_validity.replace(/%s/, (index + 1));
        errors.push(tool_url_validity);
      }
    }
  });

  // Step
  $.each($('.oxy-howto-maker-add-step'), function (key, el) {
    const step = key + 1;
    const step_section = $(el).find('.oxy-howto-step-header').next();

    const step_name = $(step_section).find('.oxy-data-step-name').val();
    if (step_name.trim() === '') {
      const step_name_validity = oxy_replace_occurrence(oxy_howto_trans.user_trans.step_name_validity, /%s/g, 1, step);

      errors.push(step_name_validity);
    }

    const stepdivtext = $(step_section).find('.oxy-howto-maker-add-stepdivtext');
    $.each($(stepdivtext).find(' > p[class*="oxy-howto-maker-add-steptext"] > textarea'), function (index, text) {

      let step_text = $(text).val().trim();
      if ($('#wp-content-wrap').hasClass('tmce-active')) {
        step_text = tinyMCE.get($(text).attr('id')).getContent().trim();
      }

      if (step_text === '' || step_text === '<p><br></p>') {
        const step_direction_validity = oxy_replace_occurrence(oxy_howto_trans.user_trans.step_direction_validity, /%s/g, 1, step);

        errors.push(step_direction_validity);
      }
    });

    const stepdivtip = $(step_section).find('.oxy-howto-maker-add-stepdivtip:not(:last-of-type)');
    if ($(stepdivtip).length > 0) {
      $.each($(stepdivtip).find(' > p[class*="oxy-howto-maker-add-steptip"] > textarea'), function (index, tip) {

        let step_tip = $(tip).val().trim();
        if ($('#wp-content-wrap').hasClass('tmce-active')) {
          step_tip = tinyMCE.get($(tip).attr('id')).getContent().trim();
        }

        if (step_tip === '' || step_tip === '<p><br></p>') {
          let step_tip_validity = oxy_replace_occurrence(oxy_howto_trans.user_trans.step_tip_validity, /%s/g, 2, step);
          step_tip_validity = oxy_replace_occurrence(step_tip_validity, /%s/g, 1, (index + 1));

          errors.push(step_tip_validity);
        }
      });
    }

    const step_image = $(step_section).find('[class*="oxy-howto-maker-step-image-input"]').val();
    if (step_image.trim() === '') {
      const step_image_validity = oxy_replace_occurrence(oxy_howto_trans.user_trans.step_image_validity, /%s/g, 1, step);

      errors.push(step_image_validity);
    }
  });

  // Total Time
  const $oxy_data_day = $('[name="oxy_data[day]"]');
  const $oxy_data_hour = $('[name="oxy_data[hour]"]');
  const $oxy_data_minute = $('[name="oxy_data[minute]"]');

  if (
    ($oxy_data_day.val() === '0' || $oxy_data_day.val() === '') &&
    ($oxy_data_hour.val() === '0' || $oxy_data_hour.val() === '') &&
    ($oxy_data_minute.val() === '0' || $oxy_data_minute.val() === '')
  ) {
    errors.push(oxy_howto_trans.user_trans.total_time_validity);
  }

  return errors;
}

function reorder_steps() {
  const $ = jQuery;
  const oxy_scroll_tos = [];
  $.each($('#oxy-howto-maker-step-section .oxy-howto-maker-add-step'), function (key, el) {
    // const key = parseInt($(cloned_el).find('.oxy-howto-step-header').attr('data-step'));
    const step_header = $(el).find('.oxy-howto-step-header');
    const step = key + 1;
    $(step_header).attr('data-step', step);
    const step_text = $(step_header).find('span').text().replace(/\d+/, step);
    $(step_header).find('span').text(step_text);
    const target = $(step_header).attr('data-target');
    let new_target = target.replace(/\d+/, step);
    $(step_header).attr('data-target', new_target);
    const step_section = $(step_header).next();
    new_target = new_target.replace(/^#/, '');
    $(step_section).attr('id', new_target);

    oxy_scroll_tos.push({id: new_target, step: step});

    if (key > 0) {
      $(step_section).find('.delete-step').get(0).childNodes[2].nodeValue = oxy_replace_occurrence(oxy_howto_trans.user_trans.delete_step, /%s/g, 1, (key + 1));
      $(step_section).find('.delete-step').removeClass('oxy-d-none');
    }

    const new_name = $(step_section).find('.oxy-data-step-name').attr('name').replace(/\d+/, key);
    $(step_section).find('.oxy-data-step-name').attr('name', new_name);

    let new_direction = $(step_section).find('.oxy-howto-maker-add-stepdivtext > p[class*="oxy-howto-maker-add-steptext"] > textarea').attr('name');
    new_direction = oxy_replace_occurrence(new_direction, /\d+/g, 1, key);
    let new_tip = $(step_section).find('.oxy-howto-maker-add-stepdivtip > p[class*="oxy-howto-maker-add-steptip"] > textarea').attr('name');
    new_tip = oxy_replace_occurrence(new_tip, /\d+/g, 1, key);
    const new_ids = [];
    $.each($(step_section).find('.oxy-howto-maker-add-stepdivtext > p[class*="oxy-howto-maker-add-steptext"] > textarea, .oxy-howto-maker-add-stepdivtip > p[class*="oxy-howto-maker-add-steptip"] > textarea'), function (index, el3) {
      const its_class = $(this).parent().attr('class');
      let new_direction_tip;
      if (its_class.indexOf('oxy-howto-maker-add-steptext') != -1) {
        new_direction_tip = oxy_replace_occurrence(new_direction, /\d+/g, 2, index);
      } else {
        new_direction_tip = oxy_replace_occurrence(new_tip, /\d+/g, 2, index);
      }

      $(el3).attr('name', new_direction_tip);
      let new_id = new_direction_tip.replace(/]\[/g, '-');
      new_id = new_id.replace(/\[/g, '-');
      new_id = new_id.slice(0, -1);
      const old_id = $(el3).attr('id');
      $(el3).attr('id', new_id);
      $(el3).show();

      tinyMCE.execCommand('mceRemoveControl', false, new_id);
      tinyMCE.execCommand('mceRemoveEditor', false, new_id);
      tinyMCE.execCommand('mceRemoveControl', false, old_id);
      tinyMCE.execCommand('mceRemoveEditor', false, old_id);

      new_ids.push(new_id);
    });

    $.each(new_ids, function (index, new_id) {
      setTimeout(function () {
        tinyMCE.execCommand('mceAddEditor', false, new_id);
        tinyMCE.execCommand('mceFocus', false, new_id);
        tinyMCE.get(new_id).on('input', function (el) {
          $('#' + new_id).val(el.target.innerHTML);
        });
      }, 300);
    });

    // Image
    const new_image = $(step_section).find('[class*="oxy-howto-maker-step-image-input"]').attr('name').replace(/\d+/, key);
    $(step_section).find('[class*="oxy-howto-maker-step-image-input"]').attr('name', new_image);

    const new_image_class = $(step_section).find('[class*="oxy-howto-maker-step-image-input"]').attr('class').replace(/\d+/, (key + 1));
    $(step_section).find('[class*="oxy-howto-maker-step-image-input"]').attr('class', new_image_class);

    const new_image_img = $(step_section).find('[class*="oxy-howto-maker-step-image-img"]').attr('class').replace(/\d+/, (key + 1));
    $(step_section).find('[class*="oxy-howto-maker-step-image-img"]').attr('class', new_image_img);

    const image_button = $(step_section).find('button[class*="oxy-howto-maker-step-image"]');
    const new_image_button = image_button.attr('class').replace(/\d+/, (key + 1));
    const new_image_button_text = image_button.text().replace(/\d+/, (key + 1));
    $(step_section).find('button[class*="oxy-howto-maker-step-image"]').attr('class', new_image_button).text(new_image_button_text);

    const image_delete_button = $(step_section).find('.oxy-howto-maker-delete-step-image');
    const new_image_delete_button = image_delete_button.attr('class').replace(/\d+/, (key + 1));
    $(step_section).find('.oxy-howto-maker-delete-step-image').attr('class', new_image_delete_button);
    image_delete_button.get(0).childNodes[2].nodeValue = oxy_replace_occurrence(oxy_howto_trans.user_trans.delete_step_image, /%s/g, 1, (key + 1));

    const next_button = $(step_section).find('.oxy-howto-maker-add-step-x').text().replace(/\d+/, key + 2);
    $(step_section).find('.oxy-howto-maker-add-step-x').text(next_button);
  });

  $('.oxy-scroll-to').find('option').not(':first').remove();
  $.each(oxy_scroll_tos, function (index, to) {
    const $oxy_scroll_to = $('.oxy-scroll-to');
    if ($($oxy_scroll_to[0]).find('option[value="#' + to.id + '"]').length === 0) {
      $oxy_scroll_to.append('<option value="#' + to.id + '">' + to.step + '</option>');
    }
  });
}

function reorder_steps_delete(step) {
  const $ = jQuery;

  let step_number = parseInt($(step).find(' > .oxy-howto-step-header').attr('data-step')) + 1;

  if ($(step).next('.oxy-howto-maker-add-step').length) {
    $.each($(step).nextAll('.oxy-howto-maker-add-step'), function (index, el) {
      // head
      const step_header = $(el).find(' > .oxy-howto-step-header');
      $(step_header).attr('data-step', step_number);

      const step_text = $(step_header).find('span').text().replace(/\d+/, step_number);
      $(step_header).find('span').text(step_text);

      const target = $(step_header).attr('data-target');
      let new_target = target.replace(/\d+/, step_number);
      $(step_header).attr('data-target', new_target);

      // section
      const step_section = $(step_header).next();
      new_target = new_target.replace(/^#/, '');
      $(step_section).attr('id', new_target);

      $(step_section).find('.delete-step').get(0).firstChild.nodeValue = oxy_replace_occurrence(oxy_howto_trans.user_trans.delete_step, /%s/g, 1, step_number);

      const new_name = $(step_section).find('.oxy-data-step-name').attr('name').replace(/\d+/, step_number - 1);
      $(step_section).find('.oxy-data-step-name').attr('name', new_name);

      let new_direction = $(step_section).find('.oxy-howto-maker-add-stepdivtext > p[class*="oxy-howto-maker-add-steptext"] > textarea').attr('name');
      new_direction = oxy_replace_occurrence(new_direction, /\d+/g, 1, step_number - 1);
      let new_tip = $(step_section).find('.oxy-howto-maker-add-stepdivtip > p[class*="oxy-howto-maker-add-steptip"] > textarea').attr('name');
      new_tip = oxy_replace_occurrence(new_tip, /\d+/g, 1, step_number - 1);
      const new_ids = [];
      $.each($(step_section).find('.oxy-howto-maker-add-stepdivtext > p[class*="oxy-howto-maker-add-steptext"] > textarea, .oxy-howto-maker-add-stepdivtip > p[class*="oxy-howto-maker-add-steptip"] > textarea'), function (index, el3) {
        const its_class = $(this).parent().attr('class');
        let new_direction_tip;
        if (its_class.indexOf('oxy-howto-maker-add-steptext') != -1) {
          new_direction_tip = oxy_replace_occurrence(new_direction, /\d+/g, 2, index);
        } else {
          new_direction_tip = oxy_replace_occurrence(new_tip, /\d+/g, 2, index);
        }

        $(el3).attr('name', new_direction_tip);
        let new_id = new_direction_tip.replace(/]\[/g, '-');
        new_id = new_id.replace(/\[/g, '-');
        new_id = new_id.slice(0, -1);
        const old_id = $(el3).attr('id');
        $(el3).attr('id', new_id);
        $(el3).show();

        tinyMCE.execCommand('mceRemoveControl', false, new_id);
        tinyMCE.execCommand('mceRemoveEditor', false, new_id);
        tinyMCE.execCommand('mceRemoveControl', false, old_id);
        tinyMCE.execCommand('mceRemoveEditor', false, old_id);

        new_ids.push(new_id);
      });

      $.each(new_ids, function (index, new_id) {
        setTimeout(function () {
          tinyMCE.execCommand('mceAddEditor', false, new_id);
          tinyMCE.execCommand('mceFocus', false, new_id);
          tinyMCE.get(new_id).on('input', function (el) {
            $('#' + new_id).val(el.target.innerHTML);
          });
        }, 300);
      });

      // Image
      const new_image_class = $(step_section).find('[class*="oxy-howto-maker-step-image-input"]').attr('class').replace(/\d+/, step_number);
      $(step_section).find('[class*="oxy-howto-maker-step-image-input"]').attr('class', new_image_class);

      const new_image_img = $(step_section).find('[class*="oxy-howto-maker-step-image-img"]').attr('class').replace(/\d+/, step_number);
      $(step_section).find('[class*="oxy-howto-maker-step-image-img"]').attr('class', new_image_img);

      const image_button = $(step_section).find('button[class*="oxy-howto-maker-step-image"]');
      const new_image_button = image_button.attr('class').replace(/\d+/, step_number);
      const new_image_button_text = image_button.text().replace(/\d+/, step_number);
      $(step_section).find('button[class*="oxy-howto-maker-step-image"]').attr('class', new_image_button).text(new_image_button_text);

      const image_delete_button = $(step_section).find('.oxy-howto-maker-delete-step-image');
      const new_image_delete_button = image_delete_button.attr('class').replace(/\d+/, step_number);
      $(step_section).find('.oxy-howto-maker-delete-step-image').attr('class', new_image_delete_button);
      image_delete_button.get(0).firstChild.nodeValue = oxy_replace_occurrence(oxy_howto_trans.user_trans.delete_step_image, /%s/g, 1, step_number);

      const next_button = $(step_section).find('.oxy-howto-maker-add-step-x').text().replace(/\d+/, step_number + 1);
      $(step_section).find('.oxy-howto-maker-add-step-x').text(next_button);

      step_number++;
    });
  }
}

function reorder_directions($main, scrollTo) {
  const $ = jQuery;
  let stepdiv = $main.find('.oxy-howto-maker-add-stepdivtext');
  if (stepdiv.length === 0) {
    stepdiv = $($main).find('.oxy-howto-maker-add-stepdivtext');
  }

  $.each(stepdiv, function (key, el) {
    const names = $(el).find('[name]');
    $.each(names, function (key2, el2) {
      $(el2).parent().find('.oxy-number').text(oxy_howto_trans.user_trans.direction + ' ' + (key + 1));

      const delete_direction_el = $(el2).parent().find('.delete-direction');
      $(delete_direction_el).get(0).childNodes[2].nodeValue = oxy_replace_occurrence(oxy_howto_trans.user_trans.delete_direction, /%s/g, 1, (key + 1));
    });
  });

  stepdiv = $main.find('.oxy-howto-maker-add-stepdivtip');
  if (stepdiv.length === 0) {
    stepdiv = $($main).find('.oxy-howto-maker-add-stepdivtip');
  }
  $.each(stepdiv, function (key, el) {
    const names = $(el).find('[name]');
    $.each(names, function (key2, el2) {
      $(el2).parent().find('.oxy-number').text(oxy_howto_trans.user_trans.tip + ' ' + (key + 1));

      const delete_tip_el = $(el2).parent().find('.delete-tip');
      $(delete_tip_el).get(0).childNodes[2].nodeValue = oxy_replace_occurrence(oxy_howto_trans.user_trans.delete_tip, /%s/g, 1, (key + 1));
    });
  });

  stepdiv = $main.find('.oxy-howto-maker-add-stepdivtext, .oxy-howto-maker-add-stepdivtip');
  if (stepdiv.length === 0) {
    stepdiv = $($main).find('.oxy-howto-maker-add-stepdivtext, .oxy-howto-maker-add-stepdivtip');
  }

  const new_ids = [];
  const step = parseInt($($main).parent().find('.oxy-howto-step-header').attr('data-step')) - 1;
  $.each(stepdiv, function (key, el) {
    const names = $(el).find('[name]');
    $.each(names, function (key2, el2) {
      const name = $(el2).attr('name');
      let new_name = oxy_replace_occurrence(name, /\d+/g, 1, step);
      new_name = oxy_replace_occurrence(new_name, /\d+/g, 2, key);
      $(el2).attr('name', new_name);

      let new_id = new_name.replace(/]\[/g, '-');
      new_id = new_id.replace(/\[/g, '-');
      new_id = new_id.slice(0, -1);
      const old_id = $(el2).attr('id');
      $(el2).attr('id', new_id);
      $(el2).show();

      tinyMCE.execCommand('mceRemoveControl', false, new_id);
      tinyMCE.execCommand('mceRemoveEditor', false, new_id);
      tinyMCE.execCommand('mceRemoveControl', false, old_id);
      tinyMCE.execCommand('mceRemoveEditor', false, old_id);

      new_ids.push(new_id);
    });
  });

  $.each(new_ids, function (index, new_id) {
    setTimeout(function () {
      tinyMCE.execCommand('mceAddEditor', false, new_id);
      tinyMCE.execCommand('mceFocus', false, new_id);
      tinyMCE.get(new_id).on('input', function (el) {
        $('#' + new_id).val(el.target.innerHTML);
      });
    }, 150);
  });

  setTimeout(function () {
    $('html, body').animate({
      scrollTop: $(scrollTo).offset().top - 50
    }, 150);
  }, 150);
}