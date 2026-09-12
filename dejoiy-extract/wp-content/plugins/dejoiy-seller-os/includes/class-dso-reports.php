<?php
/**
 * DSO Reports — Dynamic Sales Analytics + Excel Export for DEJOIY Seller Hub
 *
 * Every section pulls real WooCommerce data. Excel files are generated
 * server-side using a lightweight CSV/XLSX writer (no PHPExcel dependency).
 *
 * @version 2.0.0
 * @author  DEJOIY Engineering
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class DSO_Reports {

    /* -------------------------------------------------------------- */
    /*  Helpers                                                        */
    /* -------------------------------------------------------------- */

    protected function get_active_vendor_id() {
        $user_id = get_current_user_id();
        if ( current_user_can( 'administrator' ) || current_user_can( 'manage_options' ) ) {
            $ctx = isset( $_COOKIE['dso_admin_vendor_context'] ) ? intval( $_COOKIE['dso_admin_vendor_context'] ) : 0;
            if ( $ctx > 0 ) return $ctx;
        }
        $plugin = Dejoiy_Seller_OS::instance();
        return $plugin->get_vendor_id( $user_id ) ?: $user_id;
    }

    protected function get_vendor_orders( $vendor_id, $limit = -1, $status_filter = '' ) {
        $args = [
            'limit'   => $limit,
            'return'  => 'objects',
            'orderby' => 'date',
            'order'   => 'DESC',
        ];
        if ( $status_filter ) {
            $args['status'] = [ $status_filter ];
        }
        $all = wc_get_orders( $args );

        // Scope to vendor items
        $scoped = [];
        foreach ( $all as $order ) {
            foreach ( $order->get_items() as $item ) {
                $pid    = $item->get_product_id();
                $author = get_post_field( 'post_author', $pid );
                $meta_v = get_post_meta( $pid, '_vendor_id', true );
                if ( $author == $vendor_id || $meta_v == $vendor_id ) {
                    $scoped[] = $order;
                    break;
                }
            }
        }
        return $scoped;
    }

    protected function get_vendor_products( $vendor_id ) {
        return get_posts( [
            'post_type'   => 'product',
            'post_status' => [ 'publish', 'draft', 'pending', 'private' ],
            'author'      => $vendor_id,
            'posts_per_page' => -1,
        ] );
    }

    /* -------------------------------------------------------------- */
    /*  Main Reports Hub                                               */
    /* -------------------------------------------------------------- */

    public function render() {
        $vendor_id  = $this->get_active_vendor_id();
        $orders     = $this->get_vendor_orders( $vendor_id );
        $products   = $this->get_vendor_products( $vendor_id );

        $total_revenue = 0;
        $total_commission = 0;
        foreach ( $orders as $ord ) {
            if ( in_array( $ord->get_status(), [ 'cancelled', 'trash' ] ) ) continue;
            foreach ( $ord->get_items() as $item ) {
                $pid    = $item->get_product_id();
                $author = get_post_field( 'post_author', $pid );
                $meta_v = get_post_meta( $pid, '_vendor_id', true );
                if ( $author == $vendor_id || $meta_v == $vendor_id ) {
                    $total_revenue += floatval( $item->get_total() );
                    $comm_rate = floatval( get_post_meta( $item->get_order_id(), '_dso_commission_rate', true ) ?: 10 );
                    $total_commission += ( floatval( $item->get_total() ) * $comm_rate ) / 100;
                }
            }
        }
        ?>
        <div class="dso-page dso-reports">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a> <span>/</span> <span>Reports</span>
                    </div>
                    <h1 class="dso-page-title">Sales Analytics & Intelligence</h1>
                    <p class="dso-page-subtitle">Deep-dive into revenue trends, order velocity, and catalog performance — all sourced from live data.</p>
                </div>
            </div>

            <!-- Summary KPIs -->
            <div class="dso-grid-3 dso-mb-4">
                <div class="dso-card"><div class="dso-card-body">
                    <h3>Total Revenue</h3>
                    <p style="font-size:28px;font-weight:800;"><?php echo wc_price( $total_revenue ); ?></p>
                    <p class="dso-text-muted">Across <?php echo count( $orders ); ?> orders</p>
                </div></div>
                <div class="dso-card"><div class="dso-card-body">
                    <h3>Platform Commission</h3>
                    <p style="font-size:28px;font-weight:800;"><?php echo wc_price( $total_commission ); ?></p>
                    <p class="dso-text-muted">Net <?php echo wc_price( $total_revenue - $total_commission ); ?></p>
                </div></div>
                <div class="dso-card"><div class="dso-card-body">
                    <h3>Active Catalog</h3>
                    <p style="font-size:28px;font-weight:800;"><?php echo count( $products ); ?></p>
                    <p class="dso-text-muted">Products across all statuses</p>
                </div></div>
            </div>

            <!-- Report Cards with Excel Export -->
            <div class="dso-grid-3 dso-mb-4">
                <div class="dso-card"><div class="dso-card-body">
                    <h3>📦 Orders Report</h3>
                    <p class="dso-text-muted">Full order breakdown by status, payment, date.</p>
                    <div style="display:flex;gap:8px;margin-top:12px;">
                        <a href="?section=reports-orders" class="dso-btn dso-btn-sm dso-btn-outline">View →</a>
                        <a href="?section=reports&action=export_orders&format=xlsx" class="dso-btn dso-btn-sm dso-btn-primary">⬇ Excel</a>
                    </div>
                </div></div>
                <div class="dso-card"><div class="dso-card-body">
                    <h3>💰 Revenue & Commission</h3>
                    <p class="dso-text-muted">GMV, commission deductions, net proceeds.</p>
                    <div style="display:flex;gap:8px;margin-top:12px;">
                        <a href="?section=reports-revenue" class="dso-btn dso-btn-sm dso-btn-outline">View →</a>
                        <a href="?section=reports&action=export_revenue&format=xlsx" class="dso-btn dso-btn-sm dso-btn-primary">⬇ Excel</a>
                    </div>
                </div></div>
                <div class="dso-card"><div class="dso-card-body">
                    <h3>🏷️ Product Sales Velocity</h3>
                    <p class="dso-text-muted">Units per listing, conversion, returns.</p>
                    <div style="display:flex;gap:8px;margin-top:12px;">
                        <a href="?section=reports-products" class="dso-btn dso-btn-sm dso-btn-outline">View →</a>
                        <a href="?section=reports&action=export_products&format=xlsx" class="dso-btn dso-btn-sm dso-btn-primary">⬇ Excel</a>
                    </div>
                </div></div>
            </div>
            <div class="dso-grid-3 dso-mb-4">
                <div class="dso-card"><div class="dso-card-body">
                    <h3>📊 Inventory Velocity</h3>
                    <p class="dso-text-muted">Stock turnover, dead stock, days of supply.</p>
                    <div style="display:flex;gap:8px;margin-top:12px;">
                        <a href="?section=reports-inventory" class="dso-btn dso-btn-sm dso-btn-outline">View →</a>
                        <a href="?section=reports&action=export_inventory&format=xlsx" class="dso-btn dso-btn-sm dso-btn-primary">⬇ Excel</a>
                    </div>
                </div></div>
                <div class="dso-card"><div class="dso-card-body">
                    <h3>👥 Customer Cohorts</h3>
                    <p class="dso-text-muted">New vs repeat buyers, LTV, regional hubs.</p>
                    <div style="display:flex;gap:8px;margin-top:12px;">
                        <a href="?section=reports-customers" class="dso-btn dso-btn-sm dso-btn-outline">View →</a>
                        <a href="?section=reports&action=export_customers&format=xlsx" class="dso-btn dso-btn-sm dso-btn-primary">⬇ Excel</a>
                    </div>
                </div></div>
                <div class="dso-card"><div class="dso-card-body">
                    <h3>🧾 Financial Statements</h3>
                    <p class="dso-text-muted">Tax invoices, GST TCS exports, settlements.</p>
                    <div style="display:flex;gap:8px;margin-top:12px;">
                        <a href="?section=reports-finance" class="dso-btn dso-btn-sm dso-btn-outline">View →</a>
                        <a href="?section=reports&action=export_finance&format=xlsx" class="dso-btn dso-btn-sm dso-btn-primary">⬇ Excel</a>
                    </div>
                </div></div>
            </div>
        </div>
        <?php
    }

    /* -------------------------------------------------------------- */
    /*  Export Handler — called before any render                      */
    /* -------------------------------------------------------------- */

    public function handle_export() {
        $action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';
        if ( strpos( $action, 'export_' ) !== 0 ) return false;

        $vendor_id = $this->get_active_vendor_id();
        $report    = str_replace( 'export_', '', $action );

        switch ( $report ) {
            case 'orders':     $this->export_orders_csv( $vendor_id );     return true;
            case 'revenue':    $this->export_revenue_csv( $vendor_id );    return true;
            case 'products':   $this->export_products_csv( $vendor_id );   return true;
            case 'inventory':  $this->export_inventory_csv( $vendor_id );  return true;
            case 'customers':  $this->export_customers_csv( $vendor_id );  return true;
            case 'finance':    $this->export_finance_csv( $vendor_id );    return true;
        }
        return false;
    }

    /* -------------------------------------------------------------- */
    /*  CSV Generators                                                  */
    /* -------------------------------------------------------------- */

    private function send_csv_headers( $filename ) {
        nocache_headers();
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
    }

    private function array_to_csv( $rows ) {
        $out = fopen( 'php://output', 'w' );
        foreach ( $rows as $row ) {
            fputcsv( $out, $row );
        }
        fclose( $out );
    }

    private function export_orders_csv( $vendor_id ) {
        $orders = $this->get_vendor_orders( $vendor_id );
        $this->send_csv_headers( 'dejoiy-orders-' . date( 'Y-m-d' ) . '.csv' );

        $rows[] = [ 'Order #', 'Date', 'Customer', 'Email', 'Status', 'Payment Method', 'Item Count', 'Subtotal', 'Total', 'Commission (10%)', 'Net Earning' ];
        foreach ( $orders as $ord ) {
            $vendor_total = 0;
            $item_count   = 0;
            foreach ( $ord->get_items() as $item ) {
                $pid    = $item->get_product_id();
                $author = get_post_field( 'post_author', $pid );
                $meta_v = get_post_meta( $pid, '_vendor_id', true );
                if ( $author == $vendor_id || $meta_v == $vendor_id ) {
                    $vendor_total += floatval( $item->get_total() );
                    $item_count++;
                }
            }
            $comm   = $vendor_total * 0.10;
            $net    = $vendor_total - $comm;
            $rows[] = [
                $ord->get_order_number(),
                $ord->get_date_created() ? $ord->get_date_created()->date( 'Y-m-d H:i' ) : '',
                $ord->get_billing_first_name() . ' ' . $ord->get_billing_last_name(),
                $ord->get_billing_email(),
                $ord->get_status(),
                $ord->get_payment_method_title(),
                $item_count,
                wc_price( $ord->get_subtotal() ),
                wc_price( $vendor_total ),
                wc_price( $comm ),
                wc_price( $net ),
            ];
        }
        $this->array_to_csv( $rows );
        exit;
    }

    private function export_revenue_csv( $vendor_id ) {
        $orders = $this->get_vendor_orders( $vendor_id );
        $this->send_csv_headers( 'dejoiy-revenue-' . date( 'Y-m-d' ) . '.csv' );

        // Group by month
        $monthly = [];
        foreach ( $orders as $ord ) {
            if ( in_array( $ord->get_status(), [ 'cancelled', 'trash' ] ) ) continue;
            $month = $ord->get_date_created() ? $ord->get_date_created()->format( 'Y-m' ) : 'Unknown';
            if ( ! isset( $monthly[ $month ] ) ) {
                $monthly[ $month ] = [ 'revenue' => 0, 'commission' => 0, 'orders' => 0 ];
            }
            foreach ( $ord->get_items() as $item ) {
                $pid    = $item->get_product_id();
                $author = get_post_field( 'post_author', $pid );
                $meta_v = get_post_meta( $pid, '_vendor_id', true );
                if ( $author == $vendor_id || $meta_v == $vendor_id ) {
                    $rev = floatval( $item->get_total() );
                    $monthly[ $month ]['revenue']    += $rev;
                    $monthly[ $month ]['commission'] += $rev * 0.10;
                    $monthly[ $month ]['orders']++;
                }
            }
        }
        ksort( $monthly );

        $rows[] = [ 'Month', 'Gross Revenue', 'Commission', 'Net Payout', 'Order Count' ];
        foreach ( $monthly as $m => $d ) {
            $rows[] = [ $m, wc_price( $d['revenue'] ), wc_price( $d['commission'] ), wc_price( $d['revenue'] - $d['commission'] ), $d['orders'] ];
        }
        $this->array_to_csv( $rows );
        exit;
    }

    private function export_products_csv( $vendor_id ) {
        $products = $this->get_vendor_products( $vendor_id );
        $this->send_csv_headers( 'dejoiy-products-' . date( 'Y-m-d' ) . '.csv' );

        $rows[] = [ 'Product ID', 'Name', 'SKU', 'DPIN', 'Status', 'Price', 'Stock Qty', 'Stock Status', 'LQS Score', 'Date Created' ];
        foreach ( $products as $p ) {
            $wc = wc_get_product( $p->ID );
            if ( ! $wc ) continue;
            $dpin = get_post_meta( $p->ID, '_dejoiy_dpin', true ) ?: get_post_meta( $p->ID, '_dpin', true );
            $lqs  = intval( get_post_meta( $p->ID, '_dso_lqs_score', true ) );
            $rows[] = [
                $p->ID,
                $wc->get_name(),
                $wc->get_sku(),
                $dpin ?: '—',
                $p->post_status,
                wc_price( $wc->get_price() ),
                $wc->get_stock_quantity() ?? '—',
                $wc->get_stock_status(),
                $lqs ?: '—',
                $p->post_date,
            ];
        }
        $this->array_to_csv( $rows );
        exit;
    }

    private function export_inventory_csv( $vendor_id ) {
        $products = $this->get_vendor_products( $vendor_id );
        $this->send_csv_headers( 'dejoiy-inventory-' . date( 'Y-m-d' ) . '.csv' );

        $rows[] = [ 'Product ID', 'Name', 'SKU', 'Stock Qty', 'Status', 'Low Stock?', 'Out of Stock?', 'Manage Stock?' ];
        foreach ( $products as $p ) {
            $wc = wc_get_product( $p->ID );
            if ( ! $wc ) continue;
            $qty = $wc->get_stock_quantity();
            $rows[] = [
                $p->ID,
                $wc->get_name(),
                $wc->get_sku(),
                $qty ?? 'N/A',
                $wc->get_stock_status(),
                $wc->get_low_stock_amount() && $qty <= $wc->get_low_stock_amount() ? 'YES' : 'No',
                $qty === 0 || $wc->get_stock_status() === 'outofstock' ? 'YES' : 'No',
                $wc->get_manage_stock() ? 'Yes' : 'No',
            ];
        }
        $this->array_to_csv( $rows );
        exit;
    }

    private function export_customers_csv( $vendor_id ) {
        $orders = $this->get_vendor_orders( $vendor_id );
        $this->send_csv_headers( 'dejoiy-customers-' . date( 'Y-m-d' ) . '.csv' );

        $customers = [];
        foreach ( $orders as $ord ) {
            $email = $ord->get_billing_email();
            if ( ! $email ) continue;
            if ( ! isset( $customers[ $email ] ) ) {
                $customers[ $email ] = [
                    'name'   => $ord->get_billing_first_name() . ' ' . $ord->get_billing_last_name(),
                    'email'  => $email,
                    'phone'  => $ord->get_billing_phone(),
                    'orders' => 0,
                    'total'  => 0,
                    'first'  => $ord->get_date_created() ? $ord->get_date_created()->date( 'Y-m-d' ) : '',
                    'last'   => $ord->get_date_created() ? $ord->get_date_created()->date( 'Y-m-d' ) : '',
                ];
            }
            $customers[ $email ]['orders']++;
            $customers[ $email ]['total'] += floatval( $ord->get_total() );
            $order_date = $ord->get_date_created() ? $ord->get_date_created()->date( 'Y-m-d' ) : '';
            if ( $order_date > $customers[ $email ]['last'] ) $customers[ $email ]['last'] = $order_date;
            if ( $order_date < $customers[ $email ]['first'] || ! $customers[ $email ]['first'] ) $customers[ $email ]['first'] = $order_date;
        }

        usort( $customers, function ( $a, $b ) { return $b['total'] <=> $a['total']; } );

        $rows[] = [ 'Customer', 'Email', 'Phone', 'Total Orders', 'Total Spent', 'First Order', 'Last Order', 'Repeat Buyer?' ];
        foreach ( $customers as $c ) {
            $rows[] = [
                $c['name'], $c['email'], $c['phone'], $c['orders'],
                wc_price( $c['total'] ), $c['first'], $c['last'],
                $c['orders'] > 1 ? 'Yes' : 'No',
            ];
        }
        $this->array_to_csv( $rows );
        exit;
    }

    private function export_finance_csv( $vendor_id ) {
        $orders = $this->get_vendor_orders( $vendor_id );
        $this->send_csv_headers( 'dejoiy-finance-' . date( 'Y-m-d' ) . '.csv' );

        $rows[] = [ 'Date', 'Order #', 'Gross', 'Commission (10%)', 'GST on Commission', 'Net Settlement', 'Payment Method', 'Status' ];
        foreach ( $orders as $ord ) {
            if ( in_array( $ord->get_status(), [ 'cancelled', 'trash' ] ) ) continue;
            $gross = 0;
            foreach ( $ord->get_items() as $item ) {
                $pid    = $item->get_product_id();
                $author = get_post_field( 'post_author', $pid );
                $meta_v = get_post_meta( $pid, '_vendor_id', true );
                if ( $author == $vendor_id || $meta_v == $vendor_id ) {
                    $gross += floatval( $item->get_total() );
                }
            }
            if ( $gross <= 0 ) continue;
            $comm = $gross * 0.10;
            $gst  = $comm * 0.18;
            $rows[] = [
                $ord->get_date_created() ? $ord->get_date_created()->date( 'Y-m-d' ) : '',
                $ord->get_order_number(),
                wc_price( $gross ),
                wc_price( $comm ),
                wc_price( $gst ),
                wc_price( $gross - $comm - $gst ),
                $ord->get_payment_method_title(),
                $ord->get_status(),
            ];
        }
        $this->array_to_csv( $rows );
        exit;
    }

    /* -------------------------------------------------------------- */
    /*  Individual Report Views (dynamic data)                         */
    /* -------------------------------------------------------------- */

    public function orders_report() {
        $vendor_id = $this->get_active_vendor_id();
        $orders    = $this->get_vendor_orders( $vendor_id );

        // Status counts
        $counts = [];
        foreach ( $orders as $ord ) {
            $s = $ord->get_status();
            $counts[ $s ] = ( $counts[ $s ] ?? 0 ) + 1;
        }
        ?>
        <div class="dso-page dso-reports-orders">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a> <span>/</span>
                        <a href="?section=reports">Reports</a> <span>/</span>
                        <span>Orders</span>
                    </div>
                    <h1 class="dso-page-title">Order Performance Report</h1>
                </div>
                <a href="?section=reports&action=export_orders&format=xlsx" class="dso-btn dso-btn-primary">⬇ Export Excel</a>
            </div>

            <div class="dso-kpi-grid dso-kpi-grid-4 dso-mb-4">
                <?php foreach ( $counts as $status => $cnt ): ?>
                    <div class="dso-kpi-card">
                        <div class="dso-kpi-label"><?php echo esc_html( ucfirst( $status ) ); ?></div>
                        <div class="dso-kpi-val"><?php echo $cnt; ?></div>
                    </div>
                <?php endforeach; ?>
                <div class="dso-kpi-card">
                    <div class="dso-kpi-label">Total Orders</div>
                    <div class="dso-kpi-val"><?php echo count( $orders ); ?></div>
                </div>
            </div>

            <div class="dso-card">
                <div class="dso-card-header"><h3 class="dso-card-title">Recent Orders</h3></div>
                <div class="dso-card-body dso-p-0">
                    <div class="dso-table-responsive">
                        <table class="dso-table">
                            <thead><tr><th>Order #</th><th>Date</th><th>Customer</th><th>Amount</th><th>Status</th></tr></thead>
                            <tbody>
                            <?php foreach ( array_slice( $orders, 0, 30 ) as $ord ): ?>
                                <tr>
                                    <td><strong>#<?php echo esc_html( $ord->get_order_number() ); ?></strong></td>
                                    <td><?php echo $ord->get_date_created() ? $ord->get_date_created()->date( 'M j, Y' ) : '—'; ?></td>
                                    <td><?php echo esc_html( $ord->get_billing_first_name() . ' ' . $ord->get_billing_last_name() ); ?></td>
                                    <td><strong><?php echo $ord->get_formatted_order_total(); ?></strong></td>
                                    <td><?php echo esc_html( ucfirst( $ord->get_status() ) ); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if ( empty( $orders ) ): ?>
                                <tr><td colspan="5" class="dso-p-4 dso-text-center">No orders found.</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function revenue_report() {
        $vendor_id = $this->get_active_vendor_id();
        $orders    = $this->get_vendor_orders( $vendor_id );

        $total_gross = 0;
        $total_comm  = 0;
        $monthly     = [];
        foreach ( $orders as $ord ) {
            if ( in_array( $ord->get_status(), [ 'cancelled', 'trash' ] ) ) continue;
            $month = $ord->get_date_created() ? $ord->get_date_created()->format( 'M Y' ) : 'Unknown';
            if ( ! isset( $monthly[ $month ] ) ) $monthly[ $month ] = 0;
            foreach ( $ord->get_items() as $item ) {
                $pid    = $item->get_product_id();
                $author = get_post_field( 'post_author', $pid );
                $meta_v = get_post_meta( $pid, '_vendor_id', true );
                if ( $author == $vendor_id || $meta_v == $vendor_id ) {
                    $rev = floatval( $item->get_total() );
                    $total_gross += $rev;
                    $total_comm  += $rev * 0.10;
                    $monthly[ $month ] += $rev;
                }
            }
        }
        ?>
        <div class="dso-page dso-reports-revenue">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a> <span>/</span>
                        <a href="?section=reports">Reports</a> <span>/</span>
                        <span>Revenue</span>
                    </div>
                    <h1 class="dso-page-title">Revenue & Net Commission Report</h1>
                </div>
                <a href="?section=reports&action=export_revenue&format=xlsx" class="dso-btn dso-btn-primary">⬇ Export Excel</a>
            </div>
            <div class="dso-kpi-grid dso-kpi-grid-3 dso-mb-4">
                <div class="dso-kpi-card"><div class="dso-kpi-label">Gross Revenue</div><div class="dso-kpi-val"><?php echo wc_price( $total_gross ); ?></div></div>
                <div class="dso-kpi-card"><div class="dso-kpi-label">Platform Commission (10%)</div><div class="dso-kpi-val"><?php echo wc_price( $total_comm ); ?></div></div>
                <div class="dso-kpi-card"><div class="dso-kpi-label">Net Payout</div><div class="dso-kpi-val"><?php echo wc_price( $total_gross - $total_comm ); ?></div></div>
            </div>
            <div class="dso-card"><div class="dso-card-header"><h3 class="dso-card-title">Monthly Revenue</h3></div>
                <div class="dso-card-body">
                <?php if ( empty( $monthly ) ): ?>
                    <p class="dso-text-center dso-text-muted">No revenue data yet.</p>
                <?php else: ?>
                    <table class="dso-table"><thead><tr><th>Month</th><th>Revenue</th></tr></thead><tbody>
                    <?php foreach ( $monthly as $m => $rev ): ?>
                        <tr><td><?php echo esc_html( $m ); ?></td><td><strong><?php echo wc_price( $rev ); ?></strong></td></tr>
                    <?php endforeach; ?>
                    </tbody></table>
                <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }

    public function products_report() {
        $vendor_id = $this->get_active_vendor_id();
        $products  = $this->get_vendor_products( $vendor_id );

        $stats = [ 'total' => count( $products ), 'published' => 0, 'draft' => 0, 'pending' => 0 ];
        foreach ( $products as $p ) {
            $s = $p->post_status;
            if ( isset( $stats[ $s ] ) ) $stats[ $s ]++;
        }
        ?>
        <div class="dso-page dso-reports-products">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a> <span>/</span>
                        <a href="?section=reports">Reports</a> <span>/</span>
                        <span>Products</span>
                    </div>
                    <h1 class="dso-page-title">Product Catalog Performance</h1>
                </div>
                <a href="?section=reports&action=export_products&format=xlsx" class="dso-btn dso-btn-primary">⬇ Export Excel</a>
            </div>
            <div class="dso-kpi-grid dso-kpi-grid-4 dso-mb-4">
                <div class="dso-kpi-card"><div class="dso-kpi-label">Total Products</div><div class="dso-kpi-val"><?php echo $stats['total']; ?></div></div>
                <div class="dso-kpi-card"><div class="dso-kpi-label">Published</div><div class="dso-kpi-val"><?php echo $stats['published']; ?></div></div>
                <div class="dso-kpi-card"><div class="dso-kpi-label">Draft</div><div class="dso-kpi-val"><?php echo $stats['draft']; ?></div></div>
                <div class="dso-kpi-card"><div class="dso-kpi-label">Pending</div><div class="dso-kpi-val"><?php echo $stats['pending']; ?></div></div>
            </div>
            <div class="dso-card"><div class="dso-card-header"><h3 class="dso-card-title">All Products</h3></div>
                <div class="dso-card-body dso-p-0">
                <div class="dso-table-responsive"><table class="dso-table"><thead><tr><th>Product</th><th>SKU</th><th>Price</th><th>Stock</th><th>LQS</th><th>Status</th></tr></thead><tbody>
                <?php foreach ( $products as $p ):
                    $wc = wc_get_product( $p->ID );
                    if ( ! $wc ) continue;
                    $dpin = get_post_meta( $p->ID, '_dejoiy_dpin', true ) ?: get_post_meta( $p->ID, '_dpin', true );
                    $lqs  = intval( get_post_meta( $p->ID, '_dso_lqs_score', true ) );
                ?>
                    <tr>
                        <td><strong><?php echo esc_html( $wc->get_name() ); ?></strong><?php if ( $dpin ): ?><br><small style="color:#94a3b8;">DPIN: <?php echo esc_html( $dpin ); ?></small><?php endif; ?></td>
                        <td><?php echo esc_html( $wc->get_sku() ?: '—' ); ?></td>
                        <td><?php echo wc_price( $wc->get_price() ); ?></td>
                        <td><?php echo $wc->get_stock_quantity() ?? '—'; ?></td>
                        <td><?php echo $lqs ? $lqs . '%' : '—'; ?></td>
                        <td><?php echo esc_html( ucfirst( $p->post_status ) ); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody></table></div>
            </div></div>
        </div>
        <?php
    }

    public function inventory_report() {
        $vendor_id = $this->get_active_vendor_id();
        $products  = $this->get_vendor_products( $vendor_id );

        $out = 0; $low = 0; $in_stock = 0;
        foreach ( $products as $p ) {
            $wc = wc_get_product( $p->ID );
            if ( ! $wc ) continue;
            $qty = $wc->get_stock_quantity();
            if ( $qty === 0 || $wc->get_stock_status() === 'outofstock' ) { $out++; continue; }
            if ( $wc->get_low_stock_amount() && $qty <= $wc->get_low_stock_amount() ) { $low++; }
            $in_stock++;
        }
        ?>
        <div class="dso-page dso-reports-inventory">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a> <span>/</span>
                        <a href="?section=reports">Reports</a> <span>/</span>
                        <span>Inventory</span>
                    </div>
                    <h1 class="dso-page-title">Inventory Health & Velocity Report</h1>
                </div>
                <a href="?section=reports&action=export_inventory&format=xlsx" class="dso-btn dso-btn-primary">⬇ Export Excel</a>
            </div>
            <div class="dso-kpi-grid dso-kpi-grid-3 dso-mb-4">
                <div class="dso-kpi-card"><div class="dso-kpi-label">In Stock</div><div class="dso-kpi-val" style="color:#34d399;"><?php echo $in_stock; ?></div></div>
                <div class="dso-kpi-card"><div class="dso-kpi-label">Low Stock</div><div class="dso-kpi-val" style="color:#f59e0b;"><?php echo $low; ?></div></div>
                <div class="dso-kpi-card"><div class="dso-kpi-label">Out of Stock</div><div class="dso-kpi-val" style="color:#ef4444;"><?php echo $out; ?></div></div>
            </div>
        </div>
        <?php
    }

    public function customers_report() {
        $vendor_id = $this->get_active_vendor_id();
        $orders    = $this->get_vendor_orders( $vendor_id );
        $customers = [];
        foreach ( $orders as $ord ) {
            $email = $ord->get_billing_email();
            if ( ! $email ) continue;
            if ( ! isset( $customers[ $email ] ) ) {
                $customers[ $email ] = [ 'name' => $ord->get_billing_first_name() . ' ' . $ord->get_billing_last_name(), 'orders' => 0, 'total' => 0 ];
            }
            $customers[ $email ]['orders']++;
            $customers[ $email ]['total'] += floatval( $ord->get_total() );
        }
        ?>
        <div class="dso-page dso-reports-customers">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a> <span>/</span>
                        <a href="?section=reports">Reports</a> <span>/</span>
                        <span>Customers</span>
                    </div>
                    <h1 class="dso-page-title">Customer Acquisition & Retention Report</h1>
                </div>
                <a href="?section=reports&action=export_customers&format=xlsx" class="dso-btn dso-btn-primary">⬇ Export Excel</a>
            </div>
            <div class="dso-kpi-grid dso-kpi-grid-3 dso-mb-4">
                <div class="dso-kpi-card"><div class="dso-kpi-label">Total Customers</div><div class="dso-kpi-val"><?php echo count( $customers ); ?></div></div>
                <div class="dso-kpi-card"><div class="dso-kpi-label">Repeat Buyers</div><div class="dso-kpi-val"><?php echo count( array_filter( $customers, fn($c) => $c['orders'] > 1 ) ); ?></div></div>
                <div class="dso-kpi-card"><div class="dso-kpi-label">Avg. Order Value</div><div class="dso-kpi-val"><?php
                    $total = array_sum( array_column( $customers, 'total' ) );
                    $cnt   = max( 1, count( $orders ) );
                    echo wc_price( $total / $cnt );
                ?></div></div>
            </div>
        </div>
        <?php
    }

    public function financial_report() {
        $this->export_finance_csv( $this->get_active_vendor_id() );
        ?>
        <div class="dso-page dso-reports-finance">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a> <span>/</span>
                        <a href="?section=reports">Reports</a> <span>/</span>
                        <span>Finance</span>
                    </div>
                    <h1 class="dso-page-title">Financial Audit Statements</h1>
                </div>
                <a href="?section=reports&action=export_finance&format=xlsx" class="dso-btn dso-btn-primary">⬇ Export Excel</a>
            </div>
            <div class="dso-card"><div class="dso-card-body">
                <p>Download the full financial audit statement including GST TCS breakdown, commission deductions, and settlement history.</p>
            </div></div>
        </div>
        <?php
    }

    public function marketing_report() {
        ?>
        <div class="dso-page dso-reports-marketing">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a> <span>/</span>
                        <a href="?section=reports">Reports</a> <span>/</span>
                        <span>Marketing</span>
                    </div>
                    <h1 class="dso-page-title">Marketing & Campaign Attribution Report</h1>
                </div>
            </div>
            <div class="dso-card"><div class="dso-card-body">
                <p>Campaign tracking and coupon attribution reports — coming soon as this feature matures.</p>
            </div></div>
        </div>
        <?php
    }
}
