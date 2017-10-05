<?php

    /*
     * If Scout is not on, advise the user
     */
    $scout = get_option( 'sailthru_scout_options' );
    $sailthru = get_option( 'sailthru_setup_options' );
    
    // check if this is an SPM widget or a Scout Widget
    if (isset ( $sailthru['sailthru_js_type'] ) && $sailthru['sailthru_js_type'] == 'personalize_js'  ) {
        $use_spm = true;
        $section = empty( $instance['sailthru_spm_section'] ) ? ' ' :  $instance['sailthru_spm_section'];
    } else {
        $use_spm = false;
        $title = empty( $instance['title'] ) ? ' ' : apply_filters( 'widget_title', $instance['title'] );
    }

?>


<?php if ($use_spm): ?>
    <div class="sailthru-spm-widget">
         <div id="<?php echo $this->id; ?>"></div>
          <script type="text/javascript">
            jQuery(function() {
                SPM.addSection('<?php echo $section; ?>', {
                    elementId: '<?php echo $this->id; ?>'
                });

                SPM.personalize({
                    timeout: 2000,
                onSuccess: function(data){
                    //console.log('Success',data);
                },
                onError: function(error) {
                    //console.log('Error', error);
                }
            });
         });
    </script>

    </div>
<?php else: ?>
<?php
        // title
        if ( ! empty( $title ) ) {
            if ( ! isset( $before_title ) ) {
                $before_title = '';
            }
            if ( ! isset( $after_title ) ) {
                $after_title = '';
            }
            echo $before_title . trim( $title ) . $after_title;
        }
    ?>

    <div id="sailthru-scout"><div class="loading"></div></div>
<?php endif; ?>
