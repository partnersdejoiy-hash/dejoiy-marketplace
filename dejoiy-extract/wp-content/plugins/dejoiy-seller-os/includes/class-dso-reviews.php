<?php
/**
 * DSO Reviews
 */
if (!defined('ABSPATH')) exit;

class DSO_Reviews {

    public function render() {
        $user_id = get_current_user_id();
        $plugin = Dejoiy_Seller_OS::instance();
        $vendor_id = $plugin->get_vendor_id($user_id);
        $data = $this->get_reviews_data($vendor_id);

        ?>
        <div class="dso-page dso-reviews">
            <div class="dso-page-header">
                <div>
                    <h1>Reviews</h1>
                    <p>Manage customer reviews for your store and products</p>
                </div>
            </div>

            <!-- Rating Overview -->
            <div class="dso-grid-2">
                <div class="dso-card dso-rating-overview">
                    <div class="dso-rating-big">
                        <span class="dso-rating-number"><?php echo $data['avg_rating'] > 0 ? number_format($data['avg_rating'], 1) : '—' ?></span>
                        <div class="dso-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="dso-star <?php echo $i <= round($data['avg_rating']) ? 'dso-star-filled' : '' ?>">★</span>
                            <?php endfor; ?>
                        </div>
                        <span class="dso-rating-count"><?php echo $data['total_reviews'] ?> reviews</span>
                    </div>
                    <div class="dso-rating-bars">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <div class="dso-rating-bar-row">
                                <span><?php $i ?>★</span>
                                <div class="dso-rating-bar"><div class="dso-rating-bar-fill" style="width: <?php echo $data['total_reviews'] > 0 ? ($data['distribution'][$i] / $data['total_reviews']) * 100 : 0 ?>%"></div></div>
                                <span><?php echo $data['distribution'][$i] ?></span>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <div class="dso-card">
                    <div class="dso-card-header"><h3>Quick Stats</h3></div>
                    <div class="dso-card-body">
                        <div class="dso-info-list">
                            <div class="dso-info-item"><span class="dso-info-label">Pending Reviews</span><span><?php echo $data['pending_count'] ?></span></div>
                            <div class="dso-info-item"><span class="dso-info-label">Approved Reviews</span><span><?php echo $data['approved_count'] ?></span></div>
                            <div class="dso-info-item"><span class="dso-info-label">Responded</span><span><?php echo $data['responded_count'] ?></span></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reviews List -->
            <div class="dso-card">
                <div class="dso-card-header"><h3>All Reviews</h3></div>
                <div class="dso-reviews-list">
                    <?php if (empty($data['reviews'])): ?>
                        <div class="dso-empty-state">
                            <div class="dso-empty-icon">⭐</div>
                            <h3>No reviews yet</h3>
                            <p>Customer reviews will appear here once they review your products.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($data['reviews'] as $review): ?>
                            <div class="dso-review-item">
                                <div class="dso-review-header">
                                    <div class="dso-review-stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <span class="dso-star <?php echo $i <= $review['rating'] ? 'dso-star-filled' : '' ?>">★</span>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="dso-review-date"><?php echo esc_html($review['date']) ?></span>
                                </div>
                                <p class="dso-review-product">Product: <?php echo esc_html($review['product']) ?></p>
                                <p class="dso-review-customer">By: <?php echo esc_html($review['customer']) ?></p>
                                <p class="dso-review-text"><?php echo esc_html($review['review']) ?></p>
                                <?php if (!empty($review['response'])): ?>
                                    <div class="dso-review-response">
                                        <strong>Your Response:</strong>
                                        <p><?php echo esc_html($review['response']) ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }

    public function get_reviews_data($vendor_id) {
        global $wpdb;

        $reviews = [];
        $avg_rating = 0;
        $total_reviews = 0;
        $pending_count = 0;
        $approved_count = 0;
        $responded_count = 0;
        $distribution = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];

        if ($vendor_id) {
            // Average rating
            $avg = $wpdb->get_var($wpdb->prepare(
                "SELECT AVG(meta_value) FROM {$wpdb->prefix}wcfm_marketplace_review_rating_meta WHERE vendor_id = %d",
                $vendor_id
            ));
            $avg_rating = $avg ? floatval($avg) : 0;

            // Counts
            $total_reviews = intval($wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}wcfm_marketplace_reviews WHERE vendor_id = %d AND approved = 1",
                $vendor_id
            )));

            $pending_count = intval($wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}wcfm_marketplace_reviews WHERE vendor_id = %d AND approved = 0",
                $vendor_id
            )));

            $approved_count = $total_reviews;

            // Reviews list
            $rows = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}wcfm_marketplace_reviews WHERE vendor_id = %d ORDER BY created_at DESC LIMIT 20",
                $vendor_id
            ));

            foreach ($rows as $row) {
                $rating = intval($row->rating ?? 0);
                if ($rating >= 1 && $rating <= 5) $distribution[$rating]++;

                $reviews[] = [
                    'rating' => $rating,
                    'review' => $row->review ?? '',
                    'customer' => get_the_author_meta('display_name', $row->author_id ?? 0),
                    'product' => get_the_title($row->product_id ?? 0),
                    'date' => $row->created_at ? date('M j, Y', strtotime($row->created_at)) : '—',
                    'response' => $row->response ?? '',
                ];
            }
        }

        return compact('avg_rating', 'total_reviews', 'pending_count', 'approved_count', 'responded_count', 'distribution', 'reviews');
    }
}
