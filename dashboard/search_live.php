<div class="portlet box blue-hoki" id="liveVisitor">
    <div class="portlet-title">
        <div class="caption">
            Live visitor
        </div>
    </div>
    <div class="portlet-body">
    	<div class="row">
        	<div class="col-md-12"><h3>Antal brugere: <span id="totalLiveVisitor"></span></h3></div>
        </div>
        <div class="row">
        	<div class="col-md-2" style="width:10%;">Samlet kurv: </div>
            <div class="col-md-10"><span id="totalPriceVisitor"></span> kr</div>
        </div>
        <div class="row campaign-visitor-row">
        	<div class="col-md-2" style="width:10%;">Kampagne: </div>
            <div class="col-md-10" id="listCampaignVisitor"></div>
        </div>
        <div class="row search-visitor-row">
        	<div class="col-md-2" style="width:10%;">Søgninger: </div>
            <div class="col-md-10" id="listSearchVisitor"></div>
        </div>
        <div class="row">
        	<div class="col-md-8">
            	<table cellpadding="0" cellspacing="0" id="listPathVisitor">
                
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-visitor-detail" tabindex="-1" role="basic" aria-hidden="true">
	<div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title">Visitor details</h4>
            </div>
            <form action="" class="form-horizontal">
                <div class="modal-body">
                    <div style="margin-bottom:10px;">
                        Page: <span class="visitor-path"></span>
                        <span class="visitor-sparator">|</span>
                        Kurv: <span class="visitor-price"></span> kr
                        <span class="visitor-sparator">|</span>
                        <a href="javascript:;" class="visitor-open-products">Show products</a>
                    </div>
                    <div class="visitor-list"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn default" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-visitor-discount" tabindex="-1" role="basic" aria-hidden="true">
	<div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title">Give discount</h4>
            </div>
            <form action="" class="form-horizontal">
                <div class="modal-body">
                    <div class="col-md-12">
                        <div class="form-group">
                            <input type="hidden" class="form-control session-id" />
                            <label>Message</label>
                            <textarea class="form-control message"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                	<button type="button" class="btn blue make-file">Send</button>
                    <button type="button" class="btn default" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style type="text/css">
	#listPathVisitor { width:100%; border-top:1px solid #333; border-bottom:1px solid #333; margin-top:20px; }
	#listPathVisitor > tbody > tr > td { padding:4px 6px; }
	#listPathVisitor > tbody > tr.visitor-odd > td { background-color:#f3f3f3; }
	.set-bold, .set-bold td { font-weight:bold; }
	.text-right { text-align:right; }
	#listCampaignVisitor span, #listSearchVisitor span {
		display:inline-block; padding-right:17px; margin-right:9px; position:relative;
	}
	#listCampaignVisitor span:after, #listSearchVisitor span:after {
		content:'|';
		display:inline-block;
		position:absolute;
		right:0;
	}
	#listCampaignVisitor span:last-child:after, #listSearchVisitor span:last-child:after {
		content:'';
	}
	.visitor-sparator td { border-top:1px solid #333; border-bottom:1px solid #333; background:#fff !important; }
	#listPathVisitor a { color:#333; }
	.open-visitor-flow { margin-right:5px; }
	.wrap-visitor-detail td {
		padding: 8px 15px !important;
		border-bottom: 1px solid #eee;
	}
	.visitor-sparator { margin-right:10px; margin-left:10px; }
	.visitor-detail-list { border-top:1px solid #333; border-bottom:1px solid #333; }
	.visitor-session { padding:4px 6px; }
	.visitor-session-odd { background-color:#f3f3f3; }
	.visitor-flow { padding:5px 0 5px 25px; display:none; }
	.visitor-flow div { padding:3px 8px; }
	.visitor-flow div:nth-child(odd) { background-color:#f3f3f3; }
	.visitor-flow div i.fa { font-size:12px; margin-right:5px; }
	
	.visitor-products { display:none; margin-bottom:30px; }
	.visitor-products table { width:70%; border-bottom:1px solid #333; border-top:1px solid #333; }
	.visitor-products td,
	.visitor-products th {
		padding: 3px 8px;	
	}
	.visitor-products th { border-bottom: 1px solid #EFEFEF; }
	.visitor-products tr:nth-child(odd) td { background-color:#f3f3f3; }
	.visitor-products tr:last-child td { border-top: 1px solid #EFEFEF; background-color:#fff; }
</style>

<script type="text/javascript">
	$(document).ready(function () {
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
		
		$(document).off('click', '.give-discount');
		$(document).on('click', '.give-discount', function () {
			var elm = $(this);
			var session = elm.attr("data-session");
			
			$('#modal-visitor-discount .session-id').val(session);
			$('#modal-visitor-discount').modal('show');
		});
		
		$(document).off('click', '.make-file');
		$(document).on('click', '.make-file', function () {
			var session = $('#modal-visitor-discount .session-id').val();
			var message = $('#modal-visitor-discount .message').val();
			var elm = $('.give-discount[data-session="'+session+'"]');
			
			$.ajax({
				type: "POST",
				url: "/files/design/php/shopify/dashboard/statistics/ajax/stats.php",
				data: {
					mode: "makeFileSession", 
					session: session,
					message: message						
				},
				dataType: 'json',
				success: function(obj){
					if (!obj.error) {
						elm.replaceWith('<a href="javascript:;" class="btn btn-xs green">Discount Sent!</a>');
						$('#modal-visitor-discount').modal('hide');
						
						$('#modal-visitor-discount .session-id').val('');
						$('#modal-visitor-discount .message').val('');
					}
				}
			});
		});
		
		$(document).off('click', '.visitor-open-products');
		$(document).on('click', '.visitor-open-products', function () {
			$('.visitor-products').slideToggle();
		});
		
		updateDataLiveVisitor();
	});
</script>