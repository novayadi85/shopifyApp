$(document).ready(function () {
		var Siteloom = {};
		var openPath = [];
		clearTimeout(Siteloom.LiveVisitor);
		
		function updateDataLiveVisitor (interval) {
			var interval = interval || true;
											
			$.ajax({
				type: "POST",
				url: "/files/design/php/shopify/dashboard/statistics/ajax/stats.php",
				data: {
					mode: "getLiveVisitor",
					openPath: openPath				
				},
				dataType: 'json',
				success: function(obj){
					$('#totalLiveVisitor').text(obj.total_visitor);
					$('#totalPriceVisitor').text(obj.total_price);
					$('#listPathVisitor').html(obj.path);
					$('#listCampaignVisitor').html(obj.campaign);
					$('#listSearchVisitor').html(obj.search);
					
					if (obj.campaign != '') {
						$('.campaign-visitor-row').show();
					}
					else {
						$('.campaign-visitor-row').hide();	
					}
					
					if (obj.search != '') {
						$('.search-visitor-row').show();
					}
					else {
						$('.search-visitor-row').hide();	
					}
					
					if (interval != 'false') {
						if ($('#liveVisitor').length) {
							Siteloom.LiveVisitor = setTimeout(updateDataLiveVisitor, 1000);
						}
						else {
							clearTimeout(Siteloom.LiveVisitor);
						}
					}
				}
			});
		}
		
		$(document).off('click', '.open-visitor-detail');
		$(document).on('click', '.open-visitor-detail', function () {
			var session = $(this).attr('data-session-id');
			var path = $(this).attr('data-path');
			var price = $(this).attr('data-price');
			
			if (session != '') {
				$('#modal-visitor-detail .visitor-path').html(path);
				$('#modal-visitor-detail .visitor-list').html('');
				$('#modal-visitor-detail .visitor-price').html(price);
				
				$.ajax({
					type: "POST",
					url: "/files/design/php/shopify/dashboard/statistics/ajax/stats.php",
					data: {
						mode: "getVisitorDetail",
						session: session					
					},
					dataType: 'html',
					success: function(html){
						$('#modal-visitor-detail .visitor-list').html(html);
						$('#modal-visitor-detail').modal('show');
					}
				});	
			}
		});
		
		$(document).off('click', '.open-visitor-flow');
		$(document).on('click', '.open-visitor-flow', function () {
			var elm = $(this);
			var path = $(this).attr('data-path');
			
			if ($('.visitor-flow[data-path="'+path+'"]').is(":visible")) {
				$('.visitor-flow[data-path="'+path+'"]').slideUp();
				elm.find('i').removeClass('fa-minus-square-o').addClass('fa-plus-square-o');
			}
			else {
				$('.visitor-flow[data-path="'+path+'"]').slideDown();
				elm.find('i').removeClass('fa-plus-square-o').addClass('fa-minus-square-o');
			}
		});
		
		$(document).off('click', '.visitor-open-products');
		$(document).on('click', '.visitor-open-products', function () {
			$('.visitor-products').slideToggle();
		});
		
		$(document).off('click', '.open-session-kurv-detail');
		$(document).on('click', '.open-session-kurv-detail', function () {
			var elm = $(this);
			var visitor = elm.attr("data-visitor");
			var session = elm.attr("data-session");
			var product = elm.attr("data-product");
			
			$('#modal-visitor-cart .visitor-product-cart').html('');
			
			if (product > 0) {
				$.ajax({
					type: "POST",
					url: "/files/design/php/shopify/dashboard/statistics/ajax/stats.php",
					data: {
						session: session, 
						mode: 'getVisitorCartDetail'						
					},
					dataType: 'html',
					success: function(html){
						$('#modal-visitor-cart .visitor-name').html(visitor);
						$('#modal-visitor-cart .visitor-product-cart').html(html);
						$('#modal-visitor-cart').modal('show');
					}
				});
			}
		});
		$(document).off('click', '.open-session-order-detail');
		$(document).on('click', '.open-session-order-detail', function () {
			var elm = $(this);
			var visitor = elm.attr("data-visitor");
			var session = elm.attr("data-memberid");
			var order = elm.attr("data-order");
			
			$('#modal-visitor-orders .visitor-orders').html('');
			
			if (order > 0) {
				$.ajax({
					type: "POST",
					url: "/files/design/php/shopify/dashboard/statistics/ajax/stats.php",
					data: {
						session: session, 
						mode: 'getVisitorOrderList'						
					},
					dataType: 'html',
					success: function(html){
						$('#modal-visitor-orders .visitor-name').html(visitor);
						$('#modal-visitor-orders .visitor-orders').html(html);
						$('#modal-visitor-orders').modal('show');
					}
				});
			}
		});
		
		$(document).off('click', '.give-discount');
		$(document).on('click', '.give-discount', function () {
			var elm = $(this);
			var session = elm.attr("data-session");
			$('#modal-visitor-discount .session-id').val(session);
			
			$.ajax({
					type: "POST",
					url: "/files/design/php/shopify/dashboard/statistics/ajax/discount.php",
					data: {
                        session:session,
						mode: 'getDiscountsList'						
					},
					dataType: 'html',
					success: function(html){
						$('#modal-visitor-discount .btn-group').html(html);
						$('#modal-visitor-discount').modal('show');
					}
				});
			
		});
		
		$(document).off('click', '.show-visitor-form');
		$(document).on('click', '.show-visitor-form', function () {
            var session = $(this).attr("data-session");
            var form = $(this).attr('data-form');
            $.ajax({
                type: "POST",
                url: "/files/design/php/shopify/dashboard/statistics/ajax/discount.php",
                data: {
                    session: session, 
                    mode: 'getFormDiscount'						
                },
				dataType: 'json',
				beforeSend:function(){
					$('#modal-visitor-discount-form .send-visitor-popup').attr('data-session','');
				},
                success: function(dataJson){
                    //$('#modal-visitor-discount-form .visitor-discount-form :input').val('');
                    $('#modal-visitor-discount-form .visitor-discount-form').find('input[type=text],select,textarea').each(function() {
                        $(this).val('');
                    })
                    $('#modal-visitor-discount-form .visitor-discount-form').hide();
                    $('#modal-visitor-discount-form .visitor-discount-form[data-form="'+form+'"]').show();
                    $('#modal-visitor-discount-form .visitor-discount-form input.visitor-popup-products.select2').select2({data:dataJson.products,multiple: true,allowClear: true});
                    $('#modal-visitor-discount-form .visitor-discount-form input.entitled_country_ids.select2').select2({data:dataJson.country,multiple: false,allowClear: true});
					$('#modal-visitor-discount-form .send-visitor-popup').attr('data-session',session);
                    $('#modal-visitor-discount').modal('hide');
                    $('#modal-visitor-discount-form').modal('show');
                }
            });

			
		});
		
		$(document).off('click', '.back-popup-option');
		$(document).on('click', '.back-popup-option', function () {
			$('#modal-visitor-discount-form').modal('hide');
			$('#modal-visitor-discount').modal('show');
        });
        
        $(document).off('click', '.send-visitor-popup');
		$(document).on('click', '.send-visitor-popup', function () {
            var session = $(this).attr('data-session');
            var form = $(".visitor-discount-form:visible");
            var dataForm =  form.find("select, textarea, input").serialize();
            $.ajax({
                type: "POST",
                url: "/files/design/php/shopify/dashboard/statistics/ajax/discount.php",
                data: {
                    session: session, 
                    dataForm:dataForm,
                    mode: 'createDiscount'						
                },
                dataType: 'json',
                success: function(xhr){
                    if(xhr.success != false){
                        $('#modal-visitor-discount-form').modal('hide');
                    }
                }
            });

			
		});

		$(document).off('click', '.sent-discount');
		$(document).on('click', '.sent-discount', function () {
            var session = $(this).attr('data-session');
            $.ajax({
                type: "POST",
                url: "/files/design/php/shopify/dashboard/catch.php",
                data: {
                    sessionId: session, 
                    cart:'',
                    method: 'getOffer'						
                },
				dataType: 'json',
				beforeSend:function(){
					$("#offer-modal .modal-body").html('');
					$("#offer-modal").find('.create-discount').attr('data-session','');
					$("#offer-modal").find('.delete-discount').attr('data-session','');
				},
                success: function(xhr){
                    if(xhr.error == false){
						$("#offer-modal .modal-title").html(xhr.title);
						$("#offer-modal .modal-body").html(xhr.html);
						$("#offer-modal").find('.create-discount').attr('data-session',session);
						$("#offer-modal").find('.delete-discount').attr('data-session',session);
						//$(".show-visitor-form").attr('data-session',session);
						
						$("#offer-modal").modal("show");
					}
                    
                }
            });

			
		});

		$(document).off('click', '.delete-discount');
		$(document).on('click', '.delete-discount', function () {
            var session = $(this).attr('data-session');
            $.ajax({
                type: "POST",
                url: "/files/design/php/shopify/dashboard/statistics/ajax/discount.php",
                data: {
                    sessionId: session, 
                    cart:'',
                    method: 'deleteDiscount'						
                },
				dataType: 'json',
                success: function(xhr){
                    $(".modal").modal("hide");
                    
                }
            });

			
		});
		
		
		updateDataLiveVisitor();
	});