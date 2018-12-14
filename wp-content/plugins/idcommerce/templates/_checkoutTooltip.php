<span>
<?php
    if ( $type == 'recurring' ) {
        switch ($recurring) {
            case 'weekly':
                switch ($limit_term) {
                    case 0:
                        _e('This is a weekly subscription. You will be charged every week until you cancel.', 'memberdeck');
                        break;
                    default:
                        printf( __('This is a weekly subscription. You will be charged for a total of %s payments.', 'memberdeck'), $term_length);
                        break;
                }
                break;
            case 'monthly':
                switch ($limit_term) {
                    case 0:
                        _e('This is a monthly subscription. You will be charged every month until you cancel.', 'memberdeck');
                        break;
                    default:
                        printf( __('This is a monthly subscription. You will be charged a total of %s payments.', 'memberdeck'), $term_length);
                        break;
                }
                break;
            case 'annual':
                switch ($limit_term) {
                    case 0:
                        _e('This is an annual subscription. You will be charged every year until you cancel.', 'memberdeck');
                        break;
                    default:
                        printf( __('This is an annual subscription. You will be charged a total of %s payments.', 'memberdeck'), $term_length);
                        break;
                }
                break;
        }
    } elseif ( $txn_type == 'preauth' ) {
        echo __('This transaction is a pre-order. You will be charged at a later time.', 'memberdeck');
    } else {
        echo __('This is a one time payment. You will be charged immediately.', 'memberdeck');
    }
?>
</span>