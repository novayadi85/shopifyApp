<!DOCTYPE html>
<html lang="da">
<head>
<meta charset="utf-8"/>
<title>www.theis-vine.dk - Theis eSolutions</title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<meta http-equiv="Content-type" content="text/html; charset=utf-8">
<meta content="" name="description"/>
<meta content="" name="author"/>
<link href="/backend/design/template_12/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
<link href="/backend/design/template_12/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="/backend/design/template_12/global/plugins/select2/select2.css"/>
<link rel="stylesheet" type="text/css" href="/backend/design/template_12/global/plugins/jquery-multi-select/css/multi-select.css"/>
<link rel="stylesheet" type="text/css" href="/files/design/php/shopify/dashboard/statistics/web/style.css"/>
</head>
<body>
<div class="container">
`<div class="row">
		<div class="col-md-12">
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
						<div class="col-md-2" style="width:10%;">Sogninger: </div>
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
			
			<div class="modal fade" id="modal-visitor-cart" tabindex="-1" role="basic" aria-hidden="true">
				<div class="modal-dialog modal-md" style="width:700px;">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
							<h4 class="modal-title">Kurv: <span class="visitor-name"></span></h4>
						</div>
						<form action="" class="form-horizontal">
							<div class="modal-body">
								<div class="visitor-product-cart"></div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn default" data-dismiss="modal">Close</button>
							</div>
						</form>
					</div>
				</div>
			</div>
			
			<div class="modal fade" id="offer-modal" tabindex="-1" role="basic" aria-hidden="true">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
					<div class="modal-header">
						<div class="text-center display-block"><h1 class="modal-title" id="exampleModalLongTitle">Modal title</h1></div>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						
					</div>
					<div class="modal-footer">
						<button type="button" data-session="" class="btn give-discount create-discount btn-success">Create New</button>
						<button type="button" data-session="" class="btn delete-discount btn-danger">Cancel</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					</div>
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
								<div class="btn-group">
									<a class="btn btn-success show-visitor-form" data-form="1">Buy x number of product and get y number of product for free</a>
									<a class="btn btn-success show-visitor-form" data-form="2">Buy x number of product and get % off</a>
									<a class="btn btn-success show-visitor-form" data-form="3">Buy x number of product and get $ off</a>
									<a class="btn btn-success show-visitor-form" data-form="4">Buy for minimum x and get $ off</a>
									<a class="btn btn-success show-visitor-form" data-form="5">Buy for minimum x and get % off</a>
									<a class="btn btn-success show-visitor-form" data-form="6">You get % off</a>
									<a class="btn btn-success show-visitor-form" data-form="7">Start chat</a>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn default" data-dismiss="modal">Close</button>
							</div>
						</form>
					</div>
				</div>
			</div>
			<div class="modal fade" id="modal-visitor-orders" tabindex="-1" role="basic" aria-hidden="true">
				<div class="modal-dialog modal-md" style="width:700px;">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
							<h4 class="modal-title">Orders: <span class="visitor-name"></span></h4>
						</div>
						<form action="" class="form-horizontal">
							<div class="modal-body">
								<div class="visitor-orders"></div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn default" data-dismiss="modal">Close</button>
							</div>
						</form>
					</div>
				</div>
			</div>
			
			
			<div class="modal modal-overflow fade" id="modal-visitor-discount-form" tabindex="-1" role="basic" aria-hidden="true">
				<div class="modal-dialog modal-md">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
							<h4 class="modal-title">Give discount</h4>
						</div>
						
							<div class="modal-body">
								<div class="col-md-12">
									<div class="visitor-discount-form" data-form="1">
										Form 1
									</div>
									<div class="visitor-discount-form" data-form="2">
										<form action="" class="form-horizontal">
											<input type="hidden" name="value_type" value="percentage">
											<input type="hidden" name="target_type" value="line_item">
											<div class="form-group">
												<label class="col-md-3">&nbsp;</label>
												<h4 class="col-md-9">Buy for minimum subtotal of order and get % off</h4>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label" style="padding-top:0;margin-top:-3px;">Choose product from basket</label>
												<div class="col-md-9">
													<input width="300" class="visitor-popup-products select2" name="entitled_product_ids" multiple="multiple">
												</div>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label" style="padding-top:0;margin-top:-3px;">Minimul Subtotal</label>
												<div class="col-md-9">
													<input name="prerequisite_subtotal_range" type="text" style="width:150px;" class="visitor-popup-number-product form-control" />
												</div>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label">% off</label>
												<div class="col-md-9">
													<input type="text" name="value" style="width:150px;" class="visitor-popup-pct-off form-control" />
												</div>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label">Voucher Code</label>
												<div class="col-md-9">
													<input name="discount_code" class="visitor-popup-voucher form-control">
												</div>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label">Headline</label>
												<div class="col-md-9">
													<input type="text" name="headline" class="visitor-popup-headline form-control" />
												</div>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label">Subheadline</label>
												<div class="col-md-9">
													<input type="text" name="subheadline" class="visitor-popup-subheadline form-control" />
												</div>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label">Message</label>
												<div class="col-md-9">
													<textarea name="message" class="visitor-popup-message form-control"></textarea>
												</div>
											</div>
										</form>
									</div>
									<div class="visitor-discount-form" data-form="3">
										<form action="" class="form-horizontal">
											<input type="hidden" name="value_type" value="fixed_amount">
											<input type="hidden" name="target_type" value="line_item">
											<div class="form-group">
												<label class="col-md-3">&nbsp;</label>
												<h4 class="col-md-9">Buy for minimum subtotal of order and get $ off</h4>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label" style="padding-top:0;margin-top:-3px;">Choose product from basket</label>
												<div class="col-md-9">
													<input width="300" class="visitor-popup-products select2" name="entitled_product_ids" multiple="multiple">
												</div>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label" style="padding-top:0;margin-top:-3px;">Minimul Subtotal</label>
												<div class="col-md-9">
													<input name="prerequisite_subtotal_range" type="text" style="width:150px;" class="visitor-popup-number-product form-control" />
												</div>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label">Amount off</label>
												<div class="col-md-9">
													<input type="text" name="value" style="width:150px;" class="visitor-popup-amount-off form-control" />
												</div>
											</div>
											
											<div class="form-group">
												<label class="col-md-3 control-label">Voucher Code</label>
												<div class="col-md-9">
													<input name="discount_code" class="visitor-popup-voucher form-control">
												</div>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label">Headline</label>
												<div class="col-md-9">
													<input type="text" name="headline" class="visitor-popup-headline form-control" />
												</div>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label">Subheadline</label>
												<div class="col-md-9">
													<input type="text" name="subheadline" class="visitor-popup-subheadline form-control" />
												</div>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label">Message</label>
												<div class="col-md-9">
													<textarea name="message" class="visitor-popup-message form-control"></textarea>
												</div>
											</div>
										</form>
									</div>
									<div class="visitor-discount-form" data-form="4">
										<form action="" class="form-horizontal">
											<input type="hidden" name="value_type" value="percentage">
											<input type="hidden" name="target_type" value="shipping_line">
											<input type="hidden" name="target_selection" value="all">
											<div class="form-group">
												<label class="col-md-3">&nbsp;</label>
												<h4 class="col-md-9">Buy for minimum subtotal of order and get Free Shipping</h4>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label" style="padding-top:0;margin-top:-3px;">Choose Country</label>
												<div class="col-md-9">
													<input width="300" class="entitled_country_ids select2" name="entitled_country_ids">
												</div>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label" style="padding-top:0;margin-top:-3px;">Minimul Subtotal</label>
												<div class="col-md-9">
													<input name="prerequisite_subtotal_range" type="text" style="width:150px;" class="visitor-popup-number-product form-control" />
												</div>
											</div>
											
											<div class="form-group">
												<label class="col-md-3 control-label">Voucher Code</label>
												<div class="col-md-9">
													<input name="discount_code" class="visitor-popup-voucher form-control">
												</div>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label">Headline</label>
												<div class="col-md-9">
													<input type="text" name="headline" class="visitor-popup-headline form-control" />
												</div>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label">Subheadline</label>
												<div class="col-md-9">
													<input type="text" name="subheadline" class="visitor-popup-subheadline form-control" />
												</div>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label">Message</label>
												<div class="col-md-9">
													<textarea name="message" class="visitor-popup-message form-control"></textarea>
												</div>
											</div>
										</form>
									</div>
									<div class="visitor-discount-form" data-form="5">
										<form action="" class="form-horizontal">
											<input type="hidden" name="value_type" value="percentage">
											<input type="hidden" name="target_type" value="line_item">
											<div class="form-group">
												<label class="col-md-3">&nbsp;</label>
												<h4 class="col-md-9">Buy for minimum x and get % off</h4>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label" style="padding-top:0;margin-top:-3px;">Choose product from basket</label>
												<div class="col-md-9">
													<input width="300" class="visitor-popup-products select2" name="entitled_product_ids" multiple="multiple">
												</div>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label" style="padding-top:0;margin-top:-3px;">Buy min number of products</label>
												<div class="col-md-9">
													<input type="text" style="width:150px;" class="visitor-popup-number-product form-control" />
												</div>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label">% off</label>
												<div class="col-md-9">
													<input type="text" name="value" style="width:150px;" class="visitor-popup-pct-off form-control" />
												</div>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label">Voucher Code</label>
												<div class="col-md-9">
													<input name="discount_code" class="visitor-popup-voucher form-control">
												</div>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label">Headline</label>
												<div class="col-md-9">
													<input type="text" name="headline" class="visitor-popup-headline form-control" />
												</div>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label">Subheadline</label>
												<div class="col-md-9">
													<input type="text" name="subheadline" class="visitor-popup-subheadline form-control" />
												</div>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label">Message</label>
												<div class="col-md-9">
													<textarea name="message" class="visitor-popup-message form-control"></textarea>
												</div>
											</div>
										</form>
									</div>
									<div class="visitor-discount-form" data-form="6">
										<form action="" class="form-horizontal">
											<input type="hidden" name="value_type" value="fixed_amount">
											<input type="hidden" name="target_type" value="line_item">
											<input type="hidden" name="target_selection" value="all">
											
											
											<div class="form-group">
												<label class="col-md-3">&nbsp;</label>
												<h4 class="col-md-9">You get % off</h4>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label">Voucher Code</label>
												<div class="col-md-9">
													<input name="discount_code" class="visitor-popup-voucher form-control">
												</div>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label" style="padding-top:0;margin-top:-3px;">Minimul Subtotal</label>
												<div class="col-md-9">
													<input name="prerequisite_subtotal_range" type="text" style="width:150px;" class="visitor-popup-number-product form-control" />
												</div>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label">Headline</label>
												<div class="col-md-9">
													<input type="text" name="headline" class="visitor-popup-headline form-control" />
												</div>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label">Subheadline</label>
												<div class="col-md-9">
													<input type="text" name="subheadline" class="visitor-popup-subheadline form-control" />
												</div>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label">Message</label>
												<div class="col-md-9">
													<textarea name="message" class="visitor-popup-message form-control"></textarea>
												</div>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label">% off</label>
												<div class="col-md-9">
													<input type="text" name="value" class="visitor-popup-pct-off form-control" />
												</div>
											</div>
										</form>
									</div>
									<div class="visitor-discount-form" data-form="7">
										Form 7
									</div>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn blue send-visitor-popup">Send popup</button>
								<button type="button" class="btn default back-popup-option">Cancel</button>
							</div>
						
					</div>
				</div>
			</div>
			
		</div>
	</div>
</div>
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.js"></script>
<script src="/backend/design/template_12/global/plugins/jquery-ui2/jquery-ui.min.js" type="text/javascript"></script>
<script src="/backend/design/template_12/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script type="text/javascript" src="/backend/design/template_12/global/plugins/select2/select2.js"></script>
<script type="text/javascript" src="/files/design/php/shopify/dashboard/statistics/web/app.js?v=<?=rand();?>"></script>

</body>
</html>