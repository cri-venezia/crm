<?php
if (!defined('ABSPATH')) exit;

class CRI_CRM_Widget_Registry extends \Elementor\Widget_Base
{

    public function get_name()
    {
        return 'cri_volunteer_registry';
    }

    public function get_title()
    {
        return __('Anagrafica Volontari', 'cri-crm');
    }

    public function get_icon()
    {
        return 'eicon-person';
    }

    public function get_categories()
    {
        return ['cri_category'];
    }

    public function get_script_depends()
    {
        return []; // Vanilla JS inline for now
    }

    public function get_style_depends()
    {
        return ['cri-chat-css']; // Reusing general styles
    }

    protected function render()
    {
        // Query Volunteers
        $args = [
            'meta_key'     => 'cri_is_volunteer',
            'meta_value'   => '1',
            'number'       => -1, // No limit for now, or add pagination later
            'orderby'      => 'display_name',
            'order'        => 'ASC',
        ];
        $user_query = new WP_User_Query($args);
        $volunteers = $user_query->get_results();
?>
        <div class="cri-registry-wrapper">
            <!-- Search Bar -->
            <div class="mb-6 relative">
                <input type="text" id="cri-registry-search"
                    class="w-full p-4 pl-10 border border-gray-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500 transition"
                    placeholder="Cerca volontario per nome, sede o qualifica...">
                <span class="dashicons dashicons-search absolute top-4 left-3 text-gray-400"></span>
            </div>

            <!-- Grid -->
            <div id="cri-registry-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if (!empty($volunteers)) : ?>
                    <?php foreach ($volunteers as $user) :
                        $sede = get_user_meta($user->ID, 'sede', true) ?: 'N/D';
                        $tags = get_user_meta($user->ID, 'cri_user_tags', true);
                        $avatar_url = get_avatar_url($user->ID, ['size' => 96]);
                    ?>
                        <div class="cri-registry-card bg-white rounded-xl shadow border border-gray-100 p-6 flex flex-col items-center text-center transition hover:shadow-lg"
                            data-search="<?php echo esc_attr(strtolower($user->display_name . ' ' . $sede . ' ' . $tags)); ?>">

                            <img src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr($user->display_name); ?>" class="w-24 h-24 rounded-full mb-4 border-4 border-red-50 object-cover">

                            <h3 class="text-xl font-bold text-gray-800 mb-1"><?php echo esc_html($user->display_name); ?></h3>
                            <p class="text-sm text-gray-500 mb-3 flex items-center gap-1">
                                <span class="dashicons dashicons-location-alt"></span> <?php echo esc_html($sede); ?>
                            </p>

                            <?php if ($tags) :
                                $tag_list = array_map('trim', explode(',', $tags));
                            ?>
                                <div class="flex flex-wrap justify-center gap-2 mb-4">
                                    <?php foreach ($tag_list as $tag) : ?>
                                        <span class="bg-red-50 text-[#CC0000] text-xs px-2 py-1 rounded-full font-medium border border-red-100">
                                            <?php echo esc_html($tag); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="mt-auto pt-4 border-t border-gray-100 w-full flex justify-center gap-4 text-gray-400">
                                <?php if ($user->user_email) : ?>
                                    <a href="mailto:<?php echo esc_attr($user->user_email); ?>" class="hover:text-[#CC0000] transition" title="Email">
                                        <span class="dashicons dashicons-email-alt"></span>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div class="col-span-full text-center py-10 text-gray-500">
                        <p>Nessun volontario trovato.</p>
                    </div>
                <?php endif; ?>
            </div>

            <p id="cri-no-results" class="hidden text-center text-gray-500 py-8">Nessun risultato trovato per la ricerca.</p>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.getElementById('cri-registry-search');
                const cards = document.querySelectorAll('.cri-registry-card');
                const noResults = document.getElementById('cri-no-results');

                searchInput.addEventListener('input', function(e) {
                    const term = e.target.value.toLowerCase();
                    let visibleCount = 0;

                    cards.forEach(card => {
                        const searchData = card.getAttribute('data-search');
                        if (searchData.includes(term)) {
                            card.style.display = 'flex'; // Restore flex layout
                            visibleCount++;
                        } else {
                            card.style.display = 'none';
                        }
                    });

                    if (visibleCount === 0) {
                        noResults.classList.remove('hidden');
                    } else {
                        noResults.classList.add('hidden');
                    }
                });
            });
        </script>
<?php
    }
}
