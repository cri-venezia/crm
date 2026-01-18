<?php
if (!defined('ABSPATH')) {
    exit;
}

class CRI_CRM_CPT
{

    public function __construct()
    {
        add_action('init', array($this, 'register_post_types'));
    }

    public function register_post_types()
    {
        $labels = array(
            'name'                  => _x('Pagine CRM', 'Post Type General Name', 'cri-crm'),
            'singular_name'         => _x('Pagina CRM', 'Post Type Singular Name', 'cri-crm'),
            'menu_name'             => __('Pagine CRM', 'cri-crm'),
            'name_admin_bar'        => __('Pagina CRM', 'cri-crm'),
            'archives'              => __('Archivio Pagine CRM', 'cri-crm'),
            'attributes'            => __('Attributi Pagina CRM', 'cri-crm'),
            'parent_item_colon'     => __('Genitore:', 'cri-crm'),
            'all_items'             => __('Tutte le Pagine', 'cri-crm'),
            'add_new_item'          => __('Aggiungi Nuova Pagina CRM', 'cri-crm'),
            'add_new'               => __('Aggiungi Nuova', 'cri-crm'),
            'new_item'              => __('Nuova Pagina', 'cri-crm'),
            'edit_item'             => __('Modifica Pagina', 'cri-crm'),
            'update_item'           => __('Aggiorna Pagina', 'cri-crm'),
            'view_item'             => __('Vedi Pagina', 'cri-crm'),
            'view_items'            => __('Vedi Pagine', 'cri-crm'),
            'search_items'          => __('Cerca Pagine CRM', 'cri-crm'),
            'not_found'             => __('Nessuna pagina trovata', 'cri-crm'),
            'not_found_in_trash'    => __('Nessuna pagina trovata nel cestino', 'cri-crm'),
            'featured_image'        => __('Immagine in evidenza', 'cri-crm'),
            'set_featured_image'    => __('Imposta immagine in evidenza', 'cri-crm'),
            'remove_featured_image' => __('Rimuovi immagine in evidenza', 'cri-crm'),
            'use_featured_image'    => __('Usa come immagine in evidenza', 'cri-crm'),
            'insert_into_item'      => __('Inserisci nella pagina', 'cri-crm'),
            'uploaded_to_this_item' => __('Caricato in questa pagina', 'cri-crm'),
            'items_list'            => __('Lista pagine', 'cri-crm'),
            'items_list_navigation' => __('Navigazione lista pagine', 'cri-crm'),
            'filter_items_list'     => __('Filtra lista pagine', 'cri-crm'),
        );
        $args = array(
            'label'                 => __('Pagina CRM', 'cri-crm'),
            'description'           => __('Pagine funzionali del CRM (Fundraising, Newsletter, ecc.)', 'cri-crm'),
            'labels'                => $labels,
            'supports'              => array('title', 'editor', 'elementor'), // Explicit Elementor support
            'hierarchical'          => true,
            'public'                => true, // Must be public to be viewed by users
            'show_ui'               => true,
            'show_in_menu'          => true, // Top level menu
            'menu_position'         => 20,
            'menu_icon'             => 'dashicons-networking', // Network/CRM icon
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => false, // CRM pages are distinct tools, no archive needed usually
            'exclude_from_search'   => true, // Don't show in site search results
            'publicly_queryable'    => true,
            'capability_type'       => 'page',
            'show_in_rest'          => true, // Block editor / REST API support
        );
        register_post_type('cri_crm_page', $args);
    }
}
