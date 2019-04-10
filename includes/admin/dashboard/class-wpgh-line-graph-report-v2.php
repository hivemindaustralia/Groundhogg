<?php
/**
 * Created by PhpStorm.
 * User: atty
 * Date: 11/27/2018
 * Time: 9:13 AM
 */

abstract class WPGH_Line_Graph_Report_V2 extends WPGH_Reporting_Widget
{

    /**
     * @var array
     */
    protected $data = [];

	/**
	 * @return string
	 */
    public function get_mode(){
        return 'time';
    }

    /**
     * Output the widget HTML
     */
    public function widget()
    {
        /*
         * Get Data from the Override method.
         */

        $data =  $this->get_data();

        if ( is_array( $data ) ){
            $data = json_encode( $data );
        }

        ?>
        <div class="report">
            <script type="text/javascript">
                jQuery(function($) {

                    /* DATA OPERATION */
                    var dataset = <?php echo $data; ?>;
                    var options = {
                        series: {
                            lines: {show: true},
                            points: {
                                radius: 3,
                                show: true
                            }
                        },
                        grid: {
                            hoverable: true
                        },
                        xaxis: {
                            mode: "<?php echo $this->get_mode(); ?>",
                            localTimezone: true
                            // timezone: "browser"
                            // timezone: "America/Chicago"
                        }
                    };

                    /* TOOL TIP */
                    var previousPoint = null, previousLabel = null;

                    $.fn.UseTooltip = function () {
                        $(this).bind("plothover", function (event, pos, item) {
                            if (item) {
                                if ((previousLabel != item.series.label) || (previousPoint != item.dataIndex)) {
                                    previousPoint = item.dataIndex;
                                    previousLabel = item.series.label;
                                    $("#tooltip").remove();
                                    var x = item.datapoint[0];
                                    var y = item.datapoint[1];
                                    var color = item.series.color;
                                    var date =  new Date(x).toDateString();
                                    showTooltip(item.pageX, item.pageY, color, "<strong>" + item.series.label + "</strong>: " + y + "<br/>" + date );
                                }
                            } else {
                                $("#tooltip").remove();
                                previousPoint = null;
                            }
                        });
                    };

                    function showTooltip(x, y, color, contents) {
                        $('<div id="tooltip">' + contents + '</div>').css({
                            position: 'absolute',
                            display: 'none',
                            top: y - 40,
                            left: x - 120,
                            border: '2px solid ' + color,
                            padding: '3px',
                            'font-size': '9px',
                            'border-radius': '5px',
                            'background-color': '#fff',
                            'font-family': 'Verdana, Arial, Helvetica, Tahoma, sans-serif',
                            opacity: 0.9
                        }).appendTo("body").fadeIn(200);
                    }

                    /* PLOT CHART */

                    if ( $( "#graph-<?php echo sanitize_key($this->name); ?>" ).width() > 0 ){
                        $.plot($("#graph-<?php echo sanitize_key($this->name); ?>"), dataset, options);
                        $("#graph-<?php echo sanitize_key($this->name); ?>").UseTooltip();
                    }

                });
            </script>
            <div id="graph-<?php echo sanitize_key($this->name); ?>" style="width:auto;height: 250px;"></div>
       </div>
    <?php
        echo $this->extra_widget_info();
    }

    /**
     * Output additional information
     *
     * @return string
     */
    protected function extra_widget_info()
    {
        return '';
    }

    /**
     * Get the graph information.
     *
     * @return array
     */
    protected function get_data()
    {
        // needs to over ride
        return array();

    }
}