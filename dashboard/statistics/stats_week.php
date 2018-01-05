<?
	$year = date("Y");
	$date = new DateTime(date("Y-m-d"));
	$week = $date->format("W");
?>
<div class="portlet box blue-hoki" id="liveVisitor">
    <div class="portlet-title">
        <div class="caption">
            Stats for this week
        </div>
    </div>
    <div class="portlet-body">
    	<div class="row">
        	<div class="col-md-12">
            	<div class="stats-period-selector">
                    <strong>Change stats: &nbsp;&nbsp;</strong>
                    Year: 
                    <select class="form-control stats-year">
                        <?
                            for ($i = (date("Y") - 1); $i <= (date("Y") + 1); $i++) {
                                $selected = "";
                                if ($year == $i) {
                                    $selected = "selected=\"selected\"";
                                }
                                
                                print "<option value=\"".$i."\" ".$selected.">".$i."</option>";	
                            }
                        ?>
                    </select>
                    Week:
                    <select class="form-control stats-week">
                        <?
                            for ($i = 1; $i <= 52; $i++) {
                                $selected = "";
                                if ($week == $i) {
                                    $selected = "selected=\"selected\"";
                                }
                                
                                print "<option value=\"".$i."\" ".$selected.">".$i."</option>";	
                            }
                        ?>
                    </select>
                    <hr />
                </div>
            </div>
        </div>
        <div class="row">
        	<div class="col-md-12">
            	<div class="stats-type-selector">
                    <a href="javascript:;" class="btn white active" data-type="checkout-flow">Checkout flow</a>
                    <a href="javascript:;" class="btn white" data-type="search">Search</a>
                    <a href="javascript:;" class="btn white" data-type="campaigns">Campaigns</a>
                    <a href="javascript:;" class="btn white" data-type="start-page">Start page</a>
                    <a href="javascript:;" class="btn white" data-type="last-page">Last page</a>
                    <a href="javascript:;" class="btn white" data-type="referer">Referer</a>
                    <a href="javascript:;" class="btn white" data-type="gclid">gclid</a>
            	</div>
                <div class="stats-days-selector">
                	<a href="javascript:;" class="btn btn-sm white" data-day="1">Monday</a>
                    <a href="javascript:;" class="btn btn-sm white" data-day="2">Tuesday</a>
                    <a href="javascript:;" class="btn btn-sm white" data-day="3">Wednesday</a>
                    <a href="javascript:;" class="btn btn-sm white" data-day="4">Thursday</a>
                    <a href="javascript:;" class="btn btn-sm white" data-day="5">Friday</a>
                    <a href="javascript:;" class="btn btn-sm white" data-day="6">Saturday</a>
                    <a href="javascript:;" class="btn btn-sm white" data-day="7">Sunday</a>
                </div>
                <hr />
            </div>
        </div>
        <div class="row">
        	<div class="col-md-12 stats-table">
            	
            </div>
        </div>
    </div>
</div>

<style type="text/css">
	.stats-period-selector select { display:inline-block; width:100px; margin-right:20px;}
	.stats-type-selector { margin-bottom:15px; }
	.btn.white { border:1px solid #bbb; background:#fff; color:#333; }
	.btn.white:hover, .btn.white.active { border:1px solid #2386ca; background:#2386ca; color:#fff; }
	.stats-table table { border-bottom:1px solid #bbb; border-top:1px solid #bbb; }
	.stats-table table tr td,
	.stats-table table tr th { padding:4px 6px; }
	.stats-table table tr:nth-child(odd) td { background-color:#f3f3f3; }
</style>

<script type="text/javascript">
	function getStatsWeek() {
		var data = {};
		data["year"] = $('.stats-year').val();
		data["week"] = $('.stats-week').val();
		data["type"] = $('.stats-type-selector a.active').attr('data-type');
		data["mode"] = "getStatsWeek";
		
		if ($('.stats-days-selector a.active').length) {
			data["day"] = [];
			
			$('.stats-days-selector a.active').each(function(i) {
				data["day"].push($(this).attr('data-day'));
			});
		}
		
		$('.stats-table').html('');
		Siteloom.progressOn();
		
		$.ajax({
			type: "POST",
			url: "/backend/system/statistics/ajax/stats.php",
			data: data,
			dataType: 'html',
			success: function(html){
				$('.stats-table').html(html);
				Siteloom.progressOff();
			}
		});
	}
	
	$(document).ready(function () {
		$('.stats-type-selector a').click(function () {
			var elm = $(this);			
			$('.stats-type-selector a').removeClass('active');
			
			elm.addClass('active');
			getStatsWeek();
		});
		
		$('.stats-days-selector a').click(function () {
			var elm = $(this);
			
			if (elm.hasClass('active')) {
				elm.removeClass('active');
			}
			else {
				elm.addClass('active');
			}
			
			getStatsWeek();
		});
		
		$('.stats-year, .stats-week').change(function () {
			getStatsWeek();
		});
		
		getStatsWeek();
	});
</script>