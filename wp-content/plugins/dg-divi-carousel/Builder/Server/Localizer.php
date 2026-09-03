<?php

namespace DICA\Server;

class Localizer
{
    private static string $handler = "dgta-table-modules";

    public function __construct()
    {
        add_action('divi_visual_builder_assets_before_enqueue_scripts', [$this, 'enqueue_localize_data']);
    }

    public function enqueue_localize_data()
    {
        wp_localize_script(
            self::$handler,
            'dicaVBLocalData',
            [
                'acf_settings'=>$this->get_acf_data(),
                'acf_gallery' => [
                    "acf_gallery_fields" => $this->get_acf_gallery_fields(),
                    "registered_image_size" => $this->get_registered_image_size()
                ],
                'library_item' => $this->df_library_items(),
                'post_data' => $this->get_postdata(),
            ]);
    }

    private function get_acf_gallery_fields()
    {
        if (!function_exists('acf_get_fields') && !function_exists('acf_get_field_groups')) {
            return [];
        }
        $field_groups = acf_get_field_groups();
        $field_labels = [];
        $field_labels['select_option']['label'] = "Select Gallery";

        if ($field_groups) {
            foreach ($field_groups as $group) {
                $fields = acf_get_fields($group['key']);
                if ($fields) {
                    foreach ($fields as $field) {
                        if (!is_array($field)) {
                            continue;
                        }
                        if ($field['type'] === 'gallery') {
                            $field_labels[$field['name']]['label'] = $field['label'];
                        }
                    }
                }
            }
        }

        return $field_labels;
    }

    public function get_registered_image_size()
    {
        $options = [];
        $sub_size = wp_get_registered_image_subsizes();
        foreach ($sub_size as $key => $value) {
            $options[$key]['label'] = $key;
        }

        return $options;
    }

    private function df_library_items()
    {
        $library_item = [];

        foreach (df_load_library() as $key => $value) {
            $library_item[$key]['label'] = $value;
        }

        return $library_item;
    }
    protected function get_acf_data(){
        $fieldsObj = [];
        if(class_exists('ACF') && get_option( 'df_general_acf_field_support' )==='1'){
            $groups = acf_get_field_groups(array('post_type' => 'post'));
            foreach($groups as $group) {
                $fieldsArr = acf_get_fields($group['key']);
                foreach ($fieldsArr as $field) {
                    $fieldsObj[$field['name']] = [
                        'label' => $field['label'],
                    ];
                }

            }
        }

        return array(
            'status'=> (get_option( 'df_general_acf_field_support' ) === '1' && class_exists('ACF'))?"active":"inactive",
            'post_releated_acf_fields'=>$fieldsObj
        );

    }
    protected function get_most_used_post_meta_keys()
    {
        global $wpdb;

        $most_used_meta_keys = get_transient('et_builder_most_used_meta_keys');
        if (false !== $most_used_meta_keys) {
            return $most_used_meta_keys;
        }

        $public_post_types = array_keys(et_builder_get_public_post_types());
        $post_types = "'" . implode("','", esc_sql($public_post_types)) . "'";

        $sql = "SELECT DISTINCT pm.meta_key FROM {$wpdb->postmeta} pm
		INNER JOIN {$wpdb->posts} p ON ( p.ID = pm.post_id AND p.post_type IN ({$post_types}) )
		WHERE pm.meta_key NOT LIKE '\_%'
		GROUP BY pm.meta_key
		ORDER BY COUNT(pm.meta_key) DESC
		LIMIT 50";

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $sql query does not use users/visitor input
        $most_used_meta_keys = $wpdb->get_col($sql);

        set_transient('et_builder_most_used_meta_keys', $most_used_meta_keys, 5 * MINUTE_IN_SECONDS);

        return $most_used_meta_keys;
    }

    private function get_postdata()
    {
        //HANDLE REGISTERED CATEGORIES
        $categories = get_categories(array(
            'hide_empty' => false,
            'number' => 100
        ));
        $category_list = array(
            array(
                'label' => __('Current Category', 'divi_flash'),
                'value' => 'current-category',
            )
        );
        foreach ($categories as $category) {
            $category_list[] = array(
                'label' => $category->name,
                'value' => $category->term_id,
                'slug' => $category->slug,
                'count' => $category->count,
            );
        }

        //HANDLE REGISTERED TAGS
        $tags = get_tags(array(
            'hide_empty' => false,
            'number' => 100
        ));

        $tag_list = array(
            array(
                'label' => __('All Tags', 'divi_flash'),
                'value' => '',
            )
        );

        foreach ($tags as $tag) {
            $tag_list[] = array(
                'label' => $tag->name,
                'value' => $tag->term_id,
                'slug' => $tag->slug,
                'count' => $tag->count,
            );
        }

        //HANDLE STICKY POST LIST
        $sticky = get_option('sticky_posts');
        $sticky_post_list=[];
        foreach ($sticky as $postid) {
            $sticky_post_list[] = array(
                'label' => get_the_title($postid),
                'value' => $postid,
            );
        }

        //HANDLE CUSTOM FIELD LIST
        $custom_fieldsArry = $this->get_most_used_post_meta_keys();
        $custom_fields[''] = array(
            'label'=> 'Select Value',
            'value'=> ''
        );
        error_log(print_r($custom_fields, true));
        if (!empty($custom_fields)) {
            foreach ($custom_fieldsArry as $field) {
                $custom_fields[$field]=array(
                    'label'=> $field,
                    'value'=> $field
                );
            }
        }


        return array(
            'categories' => $category_list,
            'tags' => $tag_list,
            'sticky_post_list' => $sticky_post_list,
            'custom_fields' => $custom_fields,
            'post_types' => $this->df_get_post_types(),
        );
    }
}

//new Localizer();
