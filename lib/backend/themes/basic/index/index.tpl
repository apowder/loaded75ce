<!--{$app->controller->test}-->
<div class="gridBg">
  <div class="summary-wrapper">
      <div class="summary-title">{$smarty.const.TEXT_SALES_SUMMARY}<form action="{$app->urlManager->createUrl('orders/process-order')}" method="get" class="go-to-order">{$smarty.const.TEXT_GO_TO_ORDER} <input type="text" class="form-control" name="orders_id"/> <button type="submit" class="btn btn-primary">{$smarty.const.TEXT_GO}</button></form><span class="summary_arrow"></span></div>
      <div class="summary-box-wrapper after">
          <div class="dashw dashw-1 after">
              <div class="summary-box summary-box-info">
                <div class="sbv">
                    <a class="sb-line sb-line-01" href="{$app->urlManager->createUrl('categories')}">{$smarty.const.TEXT_PRODUCTS}<span>{$app->controller->stats.products}</span></a>
                    <a class="sb-line sb-line-02" href="{$app->urlManager->createUrl('categories')}">{$smarty.const.TEXT_BRANDS}<span>{$app->controller->stats.manufacturers}</span></a>
                    <a class="sb-line sb-line-03" href="{$app->urlManager->createUrl('reviews')}">{$smarty.const.TEXT_REVIEWS}<span>{$app->controller->stats.reviews_confirmed}</span></a>
                    <a class="sb-line sb-line-04" href="{$app->urlManager->createUrl('reviews')}">{$smarty.const.TEXT_REVIEWS_APPROVE}<span>{$app->controller->stats.reviews_to_confirm}</span></a>
                </div>
            </div>
            <div class="summary-box summary-box-today">
              <div class="sbv">
                <a class="sb-title" href="{$app->urlManager->createUrl('orders?interval=1&fs=open')}">{$smarty.const.TEXT_TODAY}</a>
                <a class="sb-line sb-line-01" href="{$app->urlManager->createUrl('customers?date=presel&interval=1')}">{$smarty.const.TEXT_CLIENTS}<span>{$app->controller->stats.today.customers}</span></a>
                <a class="sb-line sb-line-02" href="{$app->urlManager->createUrl('orders?interval=1&fs=open')}">{$smarty.const.TEXT_ORDERS}<span>{$app->controller->stats.today.orders}</span></a>
                <a class="sb-line sb-line-03" href="{$app->urlManager->createUrl('orders?interval=1&fs=open')}"><i>{$smarty.const.TEXT_NEW_ORDERS}</i><span>{$app->controller->stats.today.orders_new}</span></a>
                <div class="sb-line sb-line-04"><i>{$smarty.const.TEXT_AVERAGE_ORDER_VALUE}</i><span>{$app->controller->stats.today.orders_avg_amount}</span></div>
                <div class="sb-line sb-line-05">{$smarty.const.TEXT_AMOUNT}<span>{$app->controller->stats.today.orders_amount}</span></div>
                <a href="{$app->urlManager->createUrl('orders?interval=1&fs=open')}" class="btn-show-orders" title="{$smarty.const.TEXT_HANDLE_ORDERS}">{$smarty.const.TEXT_HANDLE_ORDERS}</a>
              </div>
            </div>
          <div class="summary-box summary-box-week">
              <div class="sbv">
                  <a class="sb-title" href="{$app->urlManager->createUrl('orders?interval=week&fs=open')}">{$smarty.const.TEXT_WEEK}</a>
                  <a class="sb-line sb-line-01" href="{$app->urlManager->createUrl('customers?date=presel&interval=week')}">{$smarty.const.TEXT_CLIENTS}<span>{$app->controller->stats.week.customers}</span></a>
                  <a class="sb-line sb-line-02" href="{$app->urlManager->createUrl('orders?interval=week&fs=open')}">{$smarty.const.TEXT_ORDERS}<span>{$app->controller->stats.week.orders}</span></a>
                  <a class="sb-line sb-line-03" href="{$app->urlManager->createUrl('orders?interval=week&fs=open')}"><i>{$smarty.const.TEXT_NOT_PROCESSED_ORDERS}</i><span>{$app->controller->stats.week.orders_not_processed}</span></a>
                  <div class="sb-line sb-line-04"><i>{$smarty.const.TEXT_AVERAGE_ORDER_VALUE}</i><span>{$app->controller->stats.week.orders_avg_amount}</span></div>
                  <div class="sb-line sb-line-05">{$smarty.const.TEXT_AMOUNT}<span>{$app->controller->stats.week.orders_amount}</span></div>
                  <a href="{$app->urlManager->createUrl('orders?interval=week&fs=open')}" class="btn-show-orders" title="{$smarty.const.TEXT_SHOW_ORDERS}">{$smarty.const.TEXT_SHOW_ORDERS}</a>
              </div>
          </div>
          </div>
              <div class="dashw dashw-2 after">
                  <div class="summary-box summary-box-month">
              <div class="sbv">
                  <a class="sb-title" href="{$app->urlManager->createUrl('orders?interval=month&fs=open')}">{$smarty.const.TEXT_THIS_MONTH}</a>
                  <a class="sb-line sb-line-01" href="{$app->urlManager->createUrl('customers?date=presel&interval=month')}">{$smarty.const.TEXT_CLIENTS}<span>{$app->controller->stats.month.customers}</span></a>
                  <a class="sb-line sb-line-02" href="{$app->urlManager->createUrl('orders?interval=month&fs=open')}">{$smarty.const.TEXT_ORDERS}<span>{$app->controller->stats.month.orders}</span></a>
                  <a class="sb-line sb-line-03" href="{$app->urlManager->createUrl('orders?interval=month&fs=open')}"><i>{$smarty.const.TEXT_NOT_PROCESSED_ORDERS}</i><span>{$app->controller->stats.month.orders_not_processed}</span></a>
                  <div class="sb-line sb-line-04"><i>{$smarty.const.TEXT_AVERAGE_ORDER_VALUE}</i><span>{$app->controller->stats.month.orders_avg_amount}</span></div>
                  <div class="sb-line sb-line-05">{$smarty.const.TEXT_AMOUNT}<span>{$app->controller->stats.month.orders_amount}</span></div>
                  <a href="{$app->urlManager->createUrl('orders?interval=month&fs=open')}" class="btn-show-orders" title="{$smarty.const.TEXT_SHOW_ORDERS}">{$smarty.const.TEXT_SHOW_ORDERS}</a>
              </div>
          </div>
          <div class="summary-box summary-box-year">
              <div class="sbv">
                  <a class="sb-title" href="{$app->urlManager->createUrl('orders?interval=year&fs=open')}">{$smarty.const.TEXT_THIS_YEAR}</a>
                  <a class="sb-line sb-line-01" href="{$app->urlManager->createUrl('customers?date=presel&interval=year')}">{$smarty.const.TEXT_CLIENTS}<span>{$app->controller->stats.year.customers}</span></a>
                  <a class="sb-line sb-line-02" href="{$app->urlManager->createUrl('orders?interval=year&fs=open')}">{$smarty.const.TEXT_ORDERS}<span>{$app->controller->stats.year.orders}</span></a>
                  <a class="sb-line sb-line-03" href="{$app->urlManager->createUrl('orders?interval=year&fs=open')}"><i>{$smarty.const.TEXT_NOT_PROCESSED_ORDERS}</i><span>{$app->controller->stats.year.orders_not_processed}</span></a>
                  <div class="sb-line sb-line-04"><i>{$smarty.const.TEXT_AVERAGE_ORDER_VALUE}</i><span>{$app->controller->stats.year.orders_avg_amount}</span></div>
                  <div class="sb-line sb-line-05">{$smarty.const.TEXT_AMOUNT}<span>{$app->controller->stats.year.orders_amount}</span></div>
                  <a href="{$app->urlManager->createUrl('orders?interval=year&fs=open')}" class="btn-show-orders" title="{$smarty.const.TEXT_SHOW_ORDERS}">{$smarty.const.TEXT_SHOW_ORDERS}</a>
              </div>
          </div>
          <div class="summary-box summary-box-period">
              <div class="sbv">
                  <a class="sb-title" href="{$app->urlManager->createUrl('orders')}">{$smarty.const.TEXT_ALL_PERIOD}</a>
                  <a class="sb-line sb-line-01" href="{$app->urlManager->createUrl('customers')}">{$smarty.const.TEXT_CLIENTS}<span>{$app->controller->stats.all.customers}</span></a>
                  <a class="sb-line sb-line-02" href="{$app->urlManager->createUrl('orders')}">{$smarty.const.TEXT_ORDERS}<span>{$app->controller->stats.all.orders}</span></a>
                  <a class="sb-line sb-line-03" href="{$app->urlManager->createUrl('orders')}"><i>{$smarty.const.TEXT_NOT_PROCESSED_ORDERS}</i><span>{$app->controller->stats.all.orders_not_processed}</span></a>
                  <div class="sb-line sb-line-04"><i>{$smarty.const.TEXT_AVERAGE_ORDER_VALUE}</i><span>{$app->controller->stats.all.orders_avg_amount}</span></div>
                  <div class="sb-line sb-line-05">{$smarty.const.TEXT_AMOUNT}<span>{$app->controller->stats.all.orders_amount}</span></div>
                  <a href="{$app->urlManager->createUrl('orders')}" class="btn-show-orders" title="{$smarty.const.TEXT_SHOW_ORDERS}">{$smarty.const.TEXT_SHOW_ORDERS}</a>
              </div>
          </div>
              </div>          
      </div>
  </div>
</div>
<!--=== Multiple Statistics ===-->
<div class="statistic-bottom">
<div class="row">
  <div class="col-md-6">
    <div class="widget box">
      <div class="widget-header">
        <h4><i class="icon-file-text"></i> {$smarty.const.TEXT_NEW_ORDERS}</h4>
        <div class="toolbar no-padding">
          <div class="btn-group">
            <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
          </div>
        </div>
      </div>
      <div class="widget-content">
            <table class="table table-striped table-bordered table-hover table-responsive datatable-dashboard table-ordering no-footer" order_list="3" order_by="desc" data-ajax="index/order">
              <thead>
              <tr>
                  <th class="sorting">{$smarty.const.TEXT_CUSTOMERS}</th>
                  <th class="sorting">{$smarty.const.TEXT_ORDER_TOTAL}</th>
                  <th class="sorting">{$smarty.const.TEXT_ORDER_ID}</th>
                  <th class="sorting">{$smarty.const.ENTRY_POST_CODE}</th>
              </tr>
              </thead>

            </table>
        <div class="index_buttons">
          <a href="{$app->urlManager->createUrl('orders?interval=1')}" class="btn-primary btn">{$smarty.const.TEXT_HANDLE_ORDERS}</a>
          <a href="#" class="btn-refresh"><i class="icon-refresh"></i></a>
        </div>
      </div>
      <div class="divider"></div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="widget box">
      <div class="widget-header">
        <h4><i class="icon-area-chart"></i> {$smarty.const.TEXT_SALES_MONTH}</h4>
        <div class="toolbar no-padding">
          <div class="btn-group">
            <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
          </div>
        </div>
      </div>
      <div class="widget-content">
        <div class="row">
          <div class="col-md-12">
            <div id="chart_multiple" class="chart"></div>
          </div>
        </div>
      </div>
      <div class="divider"></div>
    </div>
  </div>
</div>
</div>
<!-- /Multiple Statistics -->
{if defined('SHOW_GOOGLE_MAPS')}
  {if SHOW_GOOGLE_MAPS == 'true'}
<div class="map_dashboard">
    <div id="gmap_markers" class="gmaps"></div>
    <script src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js">
    </script>    
    <script src="https://maps.googleapis.com/maps/api/js?key={$mapskey}&callback=initMap" async defer></script> 
</div>
<script type="text/javascript">
  $(function(){
    var click_map = false;
    $('body').on('click', function(){
      setTimeout(function(){
        if (click_map ) {
          $('.map_dashboard-hide').remove()
        } else {
          if (!$('.map_dashboard-hide').hasClass('map_dashboard-hide')){
            $('.map_dashboard').append('<div class="map_dashboard-hide" style="position: absolute; left: 0; top: 0; right: 0; bottom: 0"></div>')
          }
        }
        click_map = false
      }, 200)
    });
    $('.map_dashboard')
            .css('position', 'relative')
            .append('<div class="map_dashboard-hide" style="position: absolute; left: 0; top: 0; right: 0; bottom: 0"></div>')
            .on('click', function(){
              setTimeout(function(){
                click_map = true
              }, 100)
            })
  });


var map;
var geocoder;
var markers = new Array();
var delay = 1000;
var masSearch = new Array();
var max = 10;
var start = -1;
var tim;
var firstloaded = 0;
var moreLoaded = 0;
var markerCluster;
var _max_orders_count = 0;
var limit = 50;

function reloadClaster(){
  if (_max_orders_count > limit){
      markerCluster = new MarkerClusterer(map, markers,
            {
              imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m'
            });
  }
}

function loadMapData(){

	$.get("{Yii::$app->urlManager->createUrl('index/locations')}",{}, function(data){
		if (data.founded && data.founded.length > 0 && firstloaded == 0){
      _max_orders_count = data.orders_count;
			$.each(data.founded, function(i, e){
				addMarker(e, map);
			});
			firstloaded = 1;
			reloadClaster();
		}
		if (data.to_search && data.to_search.length > 0){
			masSearch = data.to_search;
			max = 10;
			start = -1;
			tim = setInterval(function(){
				var iter = 0;
				$.each(masSearch, function(i, e){
					if (i > start && iter <= max && e.status != 'OK'){
						multisearch(geocoder, map, e, 0);
						iter++;
						start++;
					}
				});

				if(masSearch.length < (start + 3 )){ // next iteration
					var tmp = new Array();
					$.each(masSearch, function(i, e){
						if(e.status == 'OVER_QUERY_LIMIT'){
							tmp.push(e);
						}
					});
					masSearch = tmp;
					start = -1;
					max = 5;
					if (masSearch.length == 0){
						moreLoaded = 1;
						clearInterval(tim);
						loadMapData();
					}
					//console.log(masSearch);
				}

			}, delay);
			
		} else {
			clearInterval(tim);
		}
	}, "json");
	
}

function initMap() { 
    map = new google.maps.Map(document.getElementById('gmap_markers'), { 
      zoom: parseFloat({$origPlace.zoom}),
      center: { lat: parseFloat({$origPlace.lat}), lng: parseFloat({$origPlace.lng}) }
    });
    geocoder = new google.maps.Geocoder();
	
	var labels = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	var labelIndex = 0;	

	loadMapData();
}    


function addMarker(location, map) {
  // Add the marker at the clicked location, and add the next-available label
  // from the array of alphabetical characters.
  if (_max_orders_count < limit){
	  markers.push(new google.maps.Marker({
		position:  {
			lat: parseFloat(location.lat),
			lng: parseFloat(location.lng)
		},
		label: "A",//labels[labelIndex++ % labels.length],
	    map: map,
		title: location.title
	  }));
  
  } else { // to be reloaded by Cluster
	  markers.push(new google.maps.Marker({
		position:  {
			lat: parseFloat(location.lat),
			lng: parseFloat(location.lng)
		},
		label: "A",//labels[labelIndex++ % labels.length],
		title: location.title
	  }));
  
  }
}


var $params;
function multisearch(geocoder, map, data, index){

  $params = new Array(
    { 
      componentRestrictions: {
        country: data.isocode,
        postalCode: data.pcode
      }
    },
    {
     address:data.address
    },
    {
     address:data.addressnocode
    }
  );
  
  //if (index == undefined) index = 0;
  if (index >= $params.length) return '9999';

    geocoder.geocode(
      $params[index]
	  , function(results, status) {
      data.status = status;
      //console.log(status);
      if (status === google.maps.GeocoderStatus.OK) {
          $.post("{Yii::$app->urlManager->createUrl('index/locations')}",
          {
          'lat':results[0].geometry.location.lat(),
          'lng':results[0].geometry.location.lng(),
          'order_id': data.orders_id
          },
          function(){});
         if (moreLoaded == 0){
          _max_orders_count++;
          addMarker({ lat:results[0].geometry.location.lat(), lng:results[0].geometry.location.lng(), title:''}, map);   
          reloadClaster();
         }        
      } else if (status === google.maps.GeocoderStatus.ZERO_RESULTS){
        index = parseInt(index)+1;
        var resp = multisearch(geocoder, map, data, index);
        if (resp == '9999'){
          $.post("{Yii::$app->urlManager->createUrl('index/locations')}",
            {
              'lat':'9999',
              'lng':'9999',
              'order_id': data.orders_id
            },
            function(){});
        }
      } else if(status === google.maps.GeocoderStatus.OVER_QUERY_LIMIT){
        delay = parseInt(delay) + 1000; 
      }
    });
    return;
}

function geocodeAddress(geocoder, map, data) {
	multisearch(geocoder, map, data, 0);
  return;
}
  </script>
  {/if}
   <br/>
      {$enabled_map['configuration_title']}&nbsp;<input type="checkbox" name="enabled_map" class="check_on_off" value="1" {if SHOW_GOOGLE_MAPS == 'true'} checked="checked" {/if}/>
      <script>
        $(document).ready(function(){
          $("input[name=enabled_map]").bootstrapSwitch(
          {
            onSwitchChange: function (element, arguments) {
              $.get('index/enable-map',{
                'configuration_id' : '{$enabled_map['configuration_id']}',
                'status' : arguments
              }, function(data, status){
                if (status == 'success'){ 
                  window.location.reload();
                }
              })
              return true;
            },
            onText: "{$smarty.const.SW_ON}",
            offText: "{$smarty.const.SW_OFF}",
            handleWidth: '38px',
            labelWidth: '24px'
          }
        )
        })
      </script>  
  {/if } 

  
  <script>
function onClickEvent(obj, table) {
    var orders_id = $(obj).find('input.cell_identify').val();
    window.location.href = "{$app->urlManager->createUrl('orders/process-order?orders_id="+orders_id+"')}";
}

$(document).ready(function() {
    var tableTr = $('.datatable-dashboard').DataTable({
        "ajax":$('.datatable-dashboard').data('ajax'),
        "searching":false,
        "paging":false,
        "info":false,
        fnDrawCallback:function(){
            $('.table tbody tr:eq(0)').click(function(e){
            e.preventDefault();
    })
        }
    });

    $(this).find('tbody').on( 'click', 'tr', function () {
        onClickEvent(this, tableTr);
        } );
    $('.btn-refresh').on('click', function(e) {
        e.preventDefault();
        var table = $('.datatable-dashboard').DataTable();
        table.ajax.reload();
    });
	$( window ).resize(function() {
       var width_map = $('.map_dashboard').width() / 1.5;
       $('.map_dashboard .gmaps').css('height', width_map);
   });
   $(window).resize();
});
$(document).ready(function(){
	var series_multiple = [
		{
			label: "{$smarty.const.TEXT_TOTAL_ORDERS}",
			data: data_blue,
			color: '#0060bf',
			lines: {
				fill: true,
				fillColor: {  colors: ['rgba(148,175,252,0.1)', 'rgba(148,175,252,0.45)'] }
			},
			points: {
				show: true
			},
			yaxis : 1
		}, {
			label: "",
			data: data_blue2,
			color: '#0060be',
			lines: {
				fill: true,
				fillColor: {  colors: ['rgba(148,175,252,0.1)', 'rgba(148,175,252,0.45)'] }
			},
			points: {
				show: true
			},
			yaxis : 1
		},{
			label: "{$smarty.const.TEXT_AVERAGE_ORDER_AMOUNT}",
			data: data_green,
			color: '#1bb901',
			yaxis : 2,
		},{
			label: "",
			data: data_green2,
			color: '#1bb900',
			yaxis : 2,
		},{
			label: "{$smarty.const.TEXT_TOTAL_AMOUNT}",
			data: data_red,
			color: '#f43c11',
			yaxis : 3
		},{
			label: "",
			data: data_red2,
			color: '#f43c10',
			yaxis : 3
		}
	];
var someFunc = function(val, axis){
   return "{$currcode_left}" + Math.ceil(val) + "{$currcode_right}" + '<span class="sep">/</span>';
}
var someFunc1 = function(val, axis){
   return Math.ceil(val);
}
	// Initialize flot
	var plot = $.plot("#chart_multiple", series_multiple, $.extend(true, { }, Plugins.getFlotDefaults(), {
	yaxes: [ {
			position : 'left',
			tickFormatter: someFunc1,
    min: 0
		}, {
			position : 'left',
			alignTicksWithAxis : 1,
			tickFormatter: someFunc,
			color:'transparent',
			axisMargin:100,
    min: 0
		},{
			position : 'left',
			alignTicksWithAxis : 1,
			tickFormatter: someFunc,
			color:'transparent',
      min: 0

		} ],
		xaxis: {
			mode: "time"
		},
		series: {
			lines: { show: true , lineWidth: 2},
			points: { show: true },
			grow: { active: true },
      dashes: {
        show: true,
        lineWidth: 2,
        dashLength: 10,
        toColor: ['#0060be', '#1bb900', '#f43c10']
      }
		},
		grid: {
			hoverable: true,
			clickable: true,
			axisMargin: -10
		},
		tooltip: true,
		tooltipOpts: {
			content: '%s: %y'
		}

	}));
        $(window).resize(function(){
            $('.summary-box-wrapper').inrow({ item1:'.sb-line i' });
        });
        $(window).resize();
});
</script>