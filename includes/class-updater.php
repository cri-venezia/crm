<?php
if (!defined('ABSPATH')) {
    exit;
}

class CRI_CRM_Updater
{
    private $slug;
    private $plugin_data;
    private $username;
    private $repo;
    private $plugin_file;
    private $github_response;

    public function __construct($plugin_file, $github_username, $github_repo)
    {
        $this->plugin_file = $plugin_file;
        $this->username = $github_username;
        $this->repo = $github_repo;
        $this->slug = plugin_basename($plugin_file); // 'cri-crm-core/cri-crm-core.php'

        add_filter('pre_set_site_transient_update_plugins', array($this, 'check_update'));
        add_filter('plugins_api', array($this, 'plugin_popup'), 10, 3);
        add_filter('upgrader_post_install', array($this, 'after_install'), 10, 3);
    }

    private function get_github_release_info()
    {
        if (!empty($this->github_response)) {
            return $this->github_response;
        }

        $url = "https://api.github.com/repos/{$this->username}/{$this->repo}/releases/latest";

        // Use a transient to reduce API calls (GitHub rate limits)
        $transient_key = 'cri_crm_github_release_' . $this->repo;
        $cached_response = get_transient($transient_key);

        if ($cached_response) {
            $this->github_response = $cached_response;
            return $this->github_response;
        }

        $args = array(
            'headers' => array(
                'User-Agent' => 'WordPress/CRI-CRM-Updater',
                'Accept' => 'application/vnd.github.v3+json'
            )
        );

        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body);

        if (isset($data->tag_name)) {
            $this->github_response = $data;
            set_transient($transient_key, $data, HOUR_IN_SECONDS); // Cache for 1 hour
            return $data;
        }

        return false;
    }

    public function check_update($transient)
    {
        if (empty($transient->checked)) {
            return $transient;
        }

        $release = $this->get_github_release_info();

        if ($release) {
            $this->plugin_data = get_plugin_data($this->plugin_file);
            $current_version = $this->plugin_data['Version'];
            $new_version = ltrim($release->tag_name, 'v'); // Remove 'v' prefix if present

            if (version_compare($current_version, $new_version, '<')) {
                $obj = new stdClass();
                $obj->slug = $this->slug;
                $obj->new_version = $new_version;
                $obj->url = $release->html_url;
                $obj->package = $release->zipball_url;
                $obj->plugin = $this->slug;

                // Add icons/banners if we had them, for now standard
                $obj->icons = array();
                $obj->banners = array();

                $transient->response[$this->slug] = $obj;
            }
        }

        return $transient;
    }

    public function plugin_popup($result, $action, $args)
    {
        if ($action !== 'plugin_information') {
            return $result;
        }

        if (!isset($args->slug) || $args->slug !== $this->slug) {
            return $result;
        }

        $release = $this->get_github_release_info();

        if ($release) {
            $plugin = new stdClass();
            $plugin->name = 'CRI CRM Core';
            $plugin->slug = $this->slug;
            $plugin->version = ltrim($release->tag_name, 'v');
            $plugin->author = 'CRI Venezia';
            $plugin->homepage = $release->html_url;
            $plugin->requires = '6.0';
            $plugin->tested = get_bloginfo('version');
            $plugin->last_updated = $release->published_at;

            // Description from Release Body (Markdown to HTML ideally, but simple nl2br for now)
            // Ideally use a Markdown parser, but let's keep it simple.
            $description = "<h3>Aggiornamento Disponibile: {$release->tag_name}</h3>";
            $description .= "<p>" . nl2br(esc_html($release->body)) . "</p>";

            $plugin->sections = array(
                'description' => $description,
                'changelog' => nl2br(esc_html($release->body))
            );

            $plugin->download_link = $release->zipball_url;

            return $plugin;
        }

        return $result;
    }

    public function after_install($response, $hook_extra, $result)
    {
        global $wp_filesystem;

        // Move the unzipped folder (usually repo-name-hash) to our plugin slug logic
        // But WP might handle this mostly. The main issue is that GitHub zips unzip to 'repo-v1.2.0' not 'cri-crm-core'.
        // So we might end up with a duplicate plugin folder. 
        // A simple fix is just to rename the folder after install if it's incorrect.

        // Actually, preventing folder rename issues is complex. 
        // For v1.3 simplified implementation, we accept that it might unzip to a new folder name
        // OR we rely on the user to fix it. 
        // BETTER: Use 'move_dir' logic.

        $install_directory = plugin_dir_path($this->plugin_file);
        $wp_filesystem->move($result['destination'], $install_directory);
        $result['destination'] = $install_directory;

        return $result;
    }
}
