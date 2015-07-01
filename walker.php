<?php
/**
 *  /!\ This is a copy of Walker_Nav_Menu_Edit class in core
 *
 * Create HTML list of nav menu input items.
 *
 * @package WordPress
 * @since 3.0.0
 * @uses Walker_Nav_Menu
 */
class Walker_Nav_Menu_Edit_Custom extends Walker_Nav_Menu  {
    /**
     * @see Walker_Nav_Menu::start_lvl()
     * @since 3.0.0
     *
     * @param string $output Passed by reference.
     */
    /**
     * Starts the list before the elements are added.
     *
     * @see Walker_Nav_Menu::start_lvl()
     *
     * @since 3.0.0
     *
     * @param string $output Passed by reference.
     * @param int    $depth  Depth of menu item. Used for padding.
     * @param array  $args   Not used.
     */
    private $rootelement;
    function start_lvl( &$output, $depth = 0, $args = array() ) {}

    /**
     * Ends the list of after the elements are added.
     *
     * @see Walker_Nav_Menu::end_lvl()
     *
     * @since 3.0.0
     *
     * @param string $output Passed by reference.
     * @param int    $depth  Depth of menu item. Used for padding.
     * @param array  $args   Not used.
     */
    function end_lvl( &$output, $depth = 0, $args = array() ) {}

    /**
     * @see Walker::start_el()
     * @since 3.0.0
     *
     * @param string $output Passed by reference. Used to append additional content.
     * @param object $item Menu item data object.
     * @param int $depth Depth of menu item. Used for padding.
     * @param object $args
     */
    function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0) {
        global $_wp_nav_menu_max_depth;
        $_wp_nav_menu_max_depth = $depth > $_wp_nav_menu_max_depth ? $depth : $_wp_nav_menu_max_depth;

        ob_start();
        $item_id = esc_attr( $item->ID );
        $removed_args = array(
            'action',
            'customlink-tab',
            'edit-menu-item',
            'menu-item',
            'page-tab',
            '_wpnonce',
        );

        $original_title = '';
        if ( 'taxonomy' == $item->type ) {
            $original_title = get_term_field( 'name', $item->object_id, $item->object, 'raw' );
            if ( is_wp_error( $original_title ) )
                $original_title = false;
        } elseif ( 'post_type' == $item->type ) {
            $original_object = get_post( $item->object_id );
            $original_title = get_the_title( $original_object->ID );
        }

        $classes = array(
            'menu-item menu-item-depth-' . $depth,
            'menu-item-' . esc_attr( $item->object ),
            'menu-item-edit-' . ( ( isset( $_GET['edit-menu-item'] ) && $item_id == $_GET['edit-menu-item'] ) ? 'active' : 'inactive'),
        );

        $title = $item->title;

        if ( ! empty( $item->_invalid ) ) {
            $classes[] = 'menu-item-invalid';
            /* translators: %s: title of menu item which is invalid */
            $title = sprintf( __( '%s (Invalid)' ), $item->title );
        } elseif ( isset( $item->post_status ) && 'draft' == $item->post_status ) {
            $classes[] = 'pending';
            /* translators: %s: title of menu item in draft status */
            $title = sprintf( __('%s (Pending)'), $item->title );
        }

        $title = ( ! isset( $item->label ) || '' == $item->label ) ? $title : $item->label;

        $submenu_text = '';
        if ( 0 == $depth )
            $submenu_text = 'style="display: none;"';

        ?>
        <li id="menu-item-<?php echo $item_id; ?>" class="<?php echo implode(' ', $classes ); ?>">
            <dl class="menu-item-bar">
                <dt class="menu-item-handle">
                    <span class="item-title"><span class="menu-item-title"><?php echo esc_html( $title ); ?></span> <span class="is-submenu" <?php echo $submenu_text; ?>><?php _e( 'sub item' ); ?></span></span>
                    <span class="item-controls">
                        <span class="item-type"><?php echo esc_html( $item->type_label ); ?></span>
                        <span class="item-order hide-if-js">
                            <a href="<?php
                                echo wp_nonce_url(
                                    add_query_arg(
                                        array(
                                            'action' => 'move-up-menu-item',
                                            'menu-item' => $item_id,
                                        ),
                                        remove_query_arg($removed_args, admin_url( 'nav-menus.php' ) )
                                    ),
                                    'move-menu_item'
                                );
                            ?>" class="item-move-up"><abbr title="<?php esc_attr_e('Move up'); ?>">&#8593;</abbr></a>
                            |
                            <a href="<?php
                                echo wp_nonce_url(
                                    add_query_arg(
                                        array(
                                            'action' => 'move-down-menu-item',
                                            'menu-item' => $item_id,
                                        ),
                                        remove_query_arg($removed_args, admin_url( 'nav-menus.php' ) )
                                    ),
                                    'move-menu_item'
                                );
                            ?>" class="item-move-down"><abbr title="<?php esc_attr_e('Move down'); ?>">&#8595;</abbr></a>
                        </span>
                        <a class="item-edit" id="edit-<?php echo $item_id; ?>" title="<?php esc_attr_e('Edit Menu Item'); ?>" href="<?php
                            echo ( isset( $_GET['edit-menu-item'] ) && $item_id == $_GET['edit-menu-item'] ) ? admin_url( 'nav-menus.php' ) : add_query_arg( 'edit-menu-item', $item_id, remove_query_arg( $removed_args, admin_url( 'nav-menus.php#menu-item-settings-' . $item_id ) ) );
                        ?>"><?php _e( 'Edit Menu Item' ); ?></a>
                    </span>
                </dt>
            </dl>

            <div class="menu-item-settings" id="menu-item-settings-<?php echo $item_id; ?>">
                <?php if( 'custom' == $item->type ) : ?>
                    <p class="field-url description description-wide">
                        <label for="edit-menu-item-url-<?php echo $item_id; ?>">
                            <?php _e( 'URL' ); ?><br />
                            <input type="text" id="edit-menu-item-url-<?php echo $item_id; ?>" class="widefat code edit-menu-item-url" name="menu-item-url[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->url ); ?>" />
                        </label>
                    </p>
                <?php endif; ?>
                <p class="description description-thin">
                    <label for="edit-menu-item-title-<?php echo $item_id; ?>">
                        <?php _e( 'Navigation Label' ); ?><br />
                        <input type="text" id="edit-menu-item-title-<?php echo $item_id; ?>" class="widefat edit-menu-item-title" name="menu-item-title[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->title ); ?>" />
                    </label>
                </p>
                <p class="description description-thin">
                    <label for="edit-menu-item-attr-title-<?php echo $item_id; ?>">
                        <?php _e( 'Title Attribute' ); ?><br />
                        <input type="text" id="edit-menu-item-attr-title-<?php echo $item_id; ?>" class="widefat edit-menu-item-attr-title" name="menu-item-attr-title[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->post_excerpt ); ?>" />
                    </label>
                </p>
                <p class="field-link-target description">
                    <label for="edit-menu-item-target-<?php echo $item_id; ?>">
                        <input type="checkbox" id="edit-menu-item-target-<?php echo $item_id; ?>" value="_blank" name="menu-item-target[<?php echo $item_id; ?>]"<?php checked( $item->target, '_blank' ); ?> />
                        <?php _e( 'Open link in a new window/tab' ); ?>
                    </label>
                </p>
                <p class="field-css-classes description description-thin">
                    <label for="edit-menu-item-classes-<?php echo $item_id; ?>">
                        <?php _e( 'CSS Classes (optional)' ); ?><br />
                        <input type="text" id="edit-menu-item-classes-<?php echo $item_id; ?>" class="widefat code edit-menu-item-classes" name="menu-item-classes[<?php echo $item_id; ?>]" value="<?php echo esc_attr( implode(' ', $item->classes ) ); ?>" />
                    </label>
                </p>
                <p class="field-xfn description description-thin">
                    <label for="edit-menu-item-xfn-<?php echo $item_id; ?>">
                        <?php _e( 'Link Relationship (XFN)' ); ?><br />
                        <input type="text" id="edit-menu-item-xfn-<?php echo $item_id; ?>" class="widefat code edit-menu-item-xfn" name="menu-item-xfn[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->xfn ); ?>" />
                    </label>
                </p>

                <p class="field-description description description-wide">
                    <label for="edit-menu-item-description-<?php echo $item_id; ?>">
                        <?php _e( 'Description' ); ?><br />
                        <textarea id="edit-menu-item-description-<?php echo $item_id; ?>" class="widefat edit-menu-item-description" rows="3" cols="20" name="menu-item-description[<?php echo $item_id; ?>]"><?php echo esc_html( $item->description ); // textarea_escaped ?></textarea>
                        <span class="description"><?php _e('The description will be displayed in the menu if the current theme supports it.'); ?></span>
                    </label>
                </p>
                <?php
                /* New fields insertion starts here */
                ?>


                <style type="text/css">
                        input[type="radio"].ui-helper-hidden-accessible{
                            visibility: hidden;
                            top: 0;
                            left: 0;
                        }
                </style>


                <p class="field-custom description description-wide">
                    <label for="edit-menu-item-subtitle-<?php echo $item_id; ?>">
                        <?php _e( 'Subtitle' ); ?><br />
                        <input type="text" id="edit-menu-item-subtitle-<?php echo $item_id; ?>" class="widefat code edit-menu-item-custom" name="menu-item-subtitle[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->subtitle ); ?>" />
                    </label>
                </p>

                <div style="width: 100%; overflow: hidden; position: relative;">


                <p class="megamenu-state field-custom description description-wide">
                    <label for="radio<?php echo $item_id; ?>">
                        <?php _e( 'Megamenu' ); ?><br />
                         <div id="radio<?php echo $item_id; ?>" class="megamenu-state description-wide slideThree">
                            <input class="megamenustate" type="checkbox" id="radio0<?php echo $item_id; ?>" name="menu-item-megamenu[<?php echo $item_id; ?>]" value="1"  <?php echo ($item->megamenu == 1)? 'checked="checked"': ''; ?> /><label for="radio0<?php echo $item_id; ?>"></label>
                        </div>
                    </label>
                </p>




                <div class="clearfix" ></div>

                <div class="description megarow-state description-wide">
                    <div class="root-field">
                        <span class="admin-forma fa fa-plus"></span>
                        <h4 class="text-root">Megamenu Settings</h4>
                        <div class="clearfix"></div>
                        <div class="admin-form">
                        <hr>
                        <div class="mt-10"></div>
                            <div class="clearfix"></div>
                            <p class="megarow-state field-custom description description-wide">
                                    <label for="megarow<?php echo $item_id; ?>">
                                    <h5 class="text-root"><?php _e( 'Megamenu row' ); ?><br /></h5>
                                    <div class="mt-10"></div>

                                        <div id="megarow<?php echo $item_id; ?>" class="megarow-state description-wide slideThree">
                                            <input class="megarow" type="checkbox" id="megarow2<?php echo $item_id; ?>" data-cond-execution="megamenu<?php echo $item_id; ?>" name="menu-item-megarow[<?php echo $item_id; ?>]" value="1"  <?php echo (($item->megarow == 1))? 'checked': ''; ?> /><label for="megarow2<?php echo $item_id; ?>"></label>

                                        </div>

                                    </label>

                                </p>
                            <div class="mt-20"></div><div class="clearfix"></div>
                            <div class="field conditional" data-cond-option="megamenu<?php echo $item_id; ?>" data-cond-value="1">
                                <p>Choose the number of columns</p>
                                <input class="css-checkbox inline-block" type="radio" id="column1<?php echo $item_id; ?>" name="menu-item-column[<?php echo $item_id; ?>]" value="1"  <?php echo (($item->column == 1) )? 'checked="checked"': ''; ?> /><label class="css-label radGroup1" for="column1<?php echo $item_id; ?>">12 </label>
                                <input class="css-checkbox inline-block" type="radio" id="column2<?php echo $item_id; ?>" name="menu-item-column[<?php echo $item_id; ?>]" value="2"  <?php echo (($item->column == 2))? 'checked="checked"': ''; ?> /><label class="css-label radGroup1" for="column2<?php echo $item_id; ?>">6 </label>
                                <input class="css-checkbox inline-block" type="radio" id="column3<?php echo $item_id; ?>" name="menu-item-column[<?php echo $item_id; ?>]" value="3"  <?php echo (($item->column == 3)|| !isset($item->column) )? 'checked="checked"': ''; ?> /><label class="css-label radGroup1" for="column3<?php echo $item_id; ?>">4 </label>
                                <input class="css-checkbox inline-block" type="radio" id="column4<?php echo $item_id; ?>" name="menu-item-column[<?php echo $item_id; ?>]" value="4"  <?php echo (($item->column == 4) )? 'checked="checked"': ''; ?> /><label class="css-label radGroup1" for="column4<?php echo $item_id; ?>">3 </label>
                                <input class="css-checkbox inline-block" type="radio" id="column6<?php echo $item_id; ?>" name="menu-item-column[<?php echo $item_id; ?>]" value="6"  <?php echo (($item->column == 6) )? 'checked="checked"': ''; ?> /><label class="css-label radGroup1" for="column6<?php echo $item_id; ?>">2 </label>
                            </div>
                            <div class="mt-20"></div><div class="clearfix"></div>
                            <div class="field conditional" data-cond-option="megamenu<?php echo $item_id; ?>" data-cond-value="1">
                                <p>Choose the number of columns for small screens-small</p>
                                <input class="css-checkbox inline-block" type="radio" id="column-small1<?php echo $item_id; ?>" name="menu-item-column-small[<?php echo $item_id; ?>]" value="1"  <?php echo (($item->column_small == 1) )? 'checked="checked"': ''; ?> /><label class="css-label radGroup1" for="column-small1<?php echo $item_id; ?>">12 </label>
                                <input class="css-checkbox inline-block" type="radio" id="column-small2<?php echo $item_id; ?>" name="menu-item-column-small[<?php echo $item_id; ?>]" value="2"  <?php echo (($item->column_small == 2) || !isset($item->column_small)) ? 'checked="checked"': ''; ?> /><label class="css-label radGroup1" for="column-small2<?php echo $item_id; ?>">6 </label>
                                <input class="css-checkbox inline-block" type="radio" id="column-small3<?php echo $item_id; ?>" name="menu-item-column-small[<?php echo $item_id; ?>]" value="3"  <?php echo (($item->column_small == 3) )? 'checked="checked"': ''; ?> /><label class="css-label radGroup1" for="column-small3<?php echo $item_id; ?>">4 </label>
                                <input class="css-checkbox inline-block" type="radio" id="column-small4<?php echo $item_id; ?>" name="menu-item-column-small[<?php echo $item_id; ?>]" value="4"  <?php echo (($item->column_small == 4) )? 'checked="checked"': ''; ?> /><label class="css-label radGroup1" for="column-small4<?php echo $item_id; ?>">3 </label>
                                <input class="css-checkbox inline-block" type="radio" id="column-small6<?php echo $item_id; ?>" name="menu-item-column-small[<?php echo $item_id; ?>]" value="6"  <?php echo (($item->column_small == 6) )? 'checked="checked"': ''; ?> /><label class="css-label radGroup1" for="column-small6<?php echo $item_id; ?>">2 </label>
                            </div>
                            <div class="mt-20"></div><div class="clearfix"></div>
                            <div class="field conditional" data-cond-option="megamenu<?php echo $item_id; ?>" data-cond-value="1">
                                <p class="text-root">Do you want to print the title</p>
                                <div>
                                    <input class="css-checkbox inline-block" type="radio" id="mega_show_title_1<?php echo $item_id; ?>" name="menu-item-mega-show-title[<?php echo $item_id; ?>]" value="true"  <?php echo (($item->mega_show_title == 'true' || !isset($item->mega_show_title)) )? 'checked="checked"': ''; ?> /><label class="css-label radGroup1" for="mega_show_title_1<?php echo $item_id; ?>">Yes </label>
                                    <input class="css-checkbox inline-block" type="radio" id="mega_show_title_2<?php echo $item_id; ?>" name="menu-item-mega-show-title[<?php echo $item_id; ?>]" value="false"  <?php echo (($item->mega_show_title == 'false'))? 'checked="checked"': ''; ?> /><label class="css-label radGroup1" for="mega_show_title_2<?php echo $item_id; ?>">No </label>
                                </div>
                            </div>
                            <div class="mt-20"></div><div class="clearfix"></div>
                            <!-- Megamenu Type -->
                            <div class="field conditional" data-cond-option="megamenu<?php echo $item_id; ?>" data-cond-value="1">
                                  <p>Choose the type of menu</p>
                                <fieldset>

                                    <input class="css-checkbox" type="radio" value="Link" data-cond-execution="megatype<?php echo $item_id; ?>" id="Link<?php echo $item_id; ?>" name="menu-item-megatype[<?php echo $item_id; ?>]" <?php echo (($item->megatype == "Link") || !isset($item->megatype))? 'checked="checked"': ''; ?>>
                                    <label class="css-label radGroup1" for="Link<?php echo $item_id; ?>"><span>Link Megamenu</span></label>

                                    <input class="css-checkbox" type="radio" value="Image"  data-cond-execution="megatype<?php echo $item_id; ?>" id="Image<?php echo $item_id; ?>" name="menu-item-megatype[<?php echo $item_id; ?>]" <?php echo ($item->megatype == "Image")? 'checked="checked"': ''; ?>>
                                    <label class="css-label radGroup1" for="Image<?php echo $item_id; ?>"><span>Image Megamenu</span></label>

                                    <input class="css-checkbox" type="radio" value="Product"   data-cond-execution="megatype<?php echo $item_id; ?>" id="Product<?php echo $item_id; ?>" name="menu-item-megatype[<?php echo $item_id; ?>]" <?php echo ($item->megatype == "Product")? 'checked="checked"': ''; ?>>
                                    <label class="css-label radGroup1" for="Product<?php echo $item_id; ?>"> <span>Custom Megamenu</span></label>

                                    <input class="css-checkbox" type="radio" value="Shortcode"   data-cond-execution="megatype<?php echo $item_id; ?>" id="Shortcode<?php echo $item_id; ?>" name="menu-item-megatype[<?php echo $item_id; ?>]" <?php echo ($item->megatype == "Shortcode")? 'checked="checked"': ''; ?>>
                                    <label class="css-label radGroup1" for="Shortcode<?php echo $item_id; ?>"> <span>Shortcode Megamenu</span></label>

                                    <input class="css-checkbox" type="radio" value="Widget"   data-cond-execution="megatype<?php echo $item_id; ?>" id="Widget<?php echo $item_id; ?>" name="menu-item-megatype[<?php echo $item_id; ?>]" <?php echo ($item->megatype == "Widget")? 'checked="checked"': ''; ?>>
                                    <label class="css-label radGroup1" for="Widget<?php echo $item_id; ?>"><span>Widget Megamenu</span></label>

                                    <input class="css-checkbox" type="radio" value="Recent_Posts"   data-cond-execution="megatype<?php echo $item_id; ?>" id="Recent_Posts<?php echo $item_id; ?>" name="menu-item-megatype[<?php echo $item_id; ?>]" <?php echo ($item->megatype == "Recent_Posts")? 'checked="checked"': ''; ?>>
                                    <label class="css-label radGroup1" for="Recent_Posts<?php echo $item_id; ?>"><span>Recent Posts Megamenu</span></label>

                                </fieldset>
                              </div>
                            <div class="mt-20"></div>
                            <div class="conditional" data-cond-option="megamenu<?php echo $item_id; ?>" data-cond-value="1">
                                <div class="mt-20"></div>
                            <div class="field conditional" data-cond-option="megatype<?php echo $item_id; ?>" data-cond-value="Product">
                              <p>Choose your Megapost</p>
                              <select id="edit-menu-item-megamenu-post-<?php echo $item_id; ?>" class="widefat code edit-menu-item-custom" name="menu-item-megamenu-post[<?php echo $item_id; ?>]">
                                <option disabled="disabled" class="disabled">Choose your Megapost</option>
                                <?php wp_reset_postdata();
                                $loop  = new WP_Query(
                                    array(
                                      //Type & Status Parameters
                                         'post_type'   => 'megamenu',
                                         'post_status'      => 'publish'
                                          )
                                      );
                                if ( $loop->have_posts() ) : while ( $loop->have_posts() ) : $loop->the_post();?>
                                        <option value="<?php the_id(); ?>" <?php selected( $item->megamenu_post, get_the_id(),true); ?> ><?php the_title(); ?></option>
                                            </div>
                                            <?php
                                    endwhile;
                                else :
                                    echo wpautop( 'Sorry, no posts were found' );
                                endif;?>
                              </select>
                            </div>
                            <div class="field conditional" data-cond-option="megatype<?php echo $item_id; ?>" data-cond-value="Widget">
                                <p>Choose a widget area </p>
                                <div>
                                    <select id="edit-menu-item-megamenu-widget-area-<?php echo $item_id; ?>" class="widefat code edit-menu-item-custom" name="menu-item-megamenu-widget-area[<?php echo $item_id; ?>]">
                                    <?php foreach ( $GLOBALS['wp_registered_sidebars'] as $sidebar ) { ?>
                                         <option value="<?php echo ucwords( $sidebar['name'] ); ?>" <?php selected( $item->megamenu_widget_area, $sidebar['name'],true); ?>>
                                                  <?php echo ucwords( $sidebar['name'] ); ?>
                                         </option>
                                    <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="field conditional" data-cond-option="megatype<?php echo $item_id; ?>" data-cond-value="Shortcode">
                            <p class="text-root">Enter your shortcode</p>
                            <div class="mt-10"></div>
                            <div>
                                <input type="text" id="edit-menu-item-megashortcode-<?php echo $item_id; ?>" class="widefat code edit-menu-item-custom" name="menu-item-megashortcode[<?php echo $item_id; ?>]" placeholder="Leave blanc to use default featured image" value="<?php echo esc_attr( $item->megashortcode ); ?>" />
                            </div>
                            </div>
                            <div class="field conditional" data-cond-option="megatype<?php echo $item_id; ?>" data-cond-value="Image">
                            <p class="text-root">Enter Image url</p>
                            <div class="mt-10"></div>
                            <div>
                                <input type="file" id="edit-menu-item-image-<?php echo $item_id; ?>" class="widefat code edit-menu-item-custom" name="menu-item-image[<?php echo $item_id; ?>]" placeholder="Leave blanc to use default featured image" value="<?php echo esc_attr( $item->image ); ?>" />
                            </div>
                        </div>
                        <div class="mt-20"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php if($depth == 1){
                    $this->rootelement = $item_id;
                }
                ?>
                <div class="clearfix" ></div>
                <div class="mt-20"></div>
                <div class="description description-wide">
                <div class="admin-form-depth-2">
                    <div>
                        <div class="field" data-cond-option="megatype<?php echo $this->rootelement; ?>" data-cond-value="Link">
                        <p class="text-root">Link icon</p>
                        <div>
                            <input type="text" id="edit-menu-item-icon-<?php echo $item_id; ?>" class="widefat code edit-menu-item-custom" name="menu-item-icon[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->icon ); ?>" />
                        </div>
                    </div>
                </div>
                </div>
                <div class="clearfix"></div>
                <div class="mt-10"></div>
                <p class="field-custom description description-wide">
                        <label for="nav_label<?php echo $item_id; ?>">
                        <?php _e( 'Navigation Label' ); ?><br />

                        <div id="nav_label<?php echo $item_id; ?>" class=" description-wide">
                            <input type="radio" id="nav_label1<?php echo $item_id; ?>" name="menu-item-nav_label[<?php echo $item_id; ?>]" value="1"  <?php echo (($item->nav_label == 1) || !isset($item->nav_label))? 'checked="checked"': ''; ?> /><label for="nav_label1<?php echo $item_id; ?>">Show</label>
                            <input type="radio" id="nav_label0<?php echo $item_id; ?>" name="menu-item-nav_label[<?php echo $item_id; ?>]" value="0"  <?php echo (($item->nav_label == 0))? 'checked="checked"': ''; ?> /><label for="nav_label0<?php echo $item_id; ?>">Hide</label>

                        </div>

                    </label>

                </p>
            <p class="field-description description description-wide"><small>----------------------------End Megamenu Settings---------------------------</small></p>
            </div>
                <?php
                /* New fields insertion ends here */
                ?>
                <p class="field-move hide-if-no-js description description-wide">
                    <label>
                        <span><?php _e( 'Move' ); ?></span>
                        <a href="#" class="menus-move-up"><?php _e( 'Up one' ); ?></a>
                        <a href="#" class="menus-move-down"><?php _e( 'Down one' ); ?></a>
                        <a href="#" class="menus-move-left"></a>
                        <a href="#" class="menus-move-right"></a>
                        <a href="#" class="menus-move-top"><?php _e( 'To the top' ); ?></a>
                    </label>
                </p>

                <div class="menu-item-actions description-wide submitbox">
                    <?php if( 'custom' != $item->type && $original_title !== false ) : ?>
                        <p class="link-to-original">
                            <?php printf( __('Original: %s'), '<a href="' . esc_attr( $item->url ) . '">' . esc_html( $original_title ) . '</a>' ); ?>
                        </p>
                    <?php endif; ?>
                    <a class="item-delete submitdelete deletion" id="delete-<?php echo $item_id; ?>" href="<?php
                    echo wp_nonce_url(
                        add_query_arg(
                            array(
                                'action' => 'delete-menu-item',
                                'menu-item' => $item_id,
                            ),
                            admin_url( 'nav-menus.php' )
                        ),
                        'delete-menu_item_' . $item_id
                    ); ?>"><?php _e( 'Remove' ); ?></a> <span class="meta-sep hide-if-no-js"> | </span> <a class="item-cancel submitcancel hide-if-no-js" id="cancel-<?php echo $item_id; ?>" href="<?php echo esc_url( add_query_arg( array( 'edit-menu-item' => $item_id, 'cancel' => time() ), admin_url( 'nav-menus.php' ) ) );
                        ?>#menu-item-settings-<?php echo $item_id; ?>"><?php _e('Cancel'); ?></a>
                </div>

                <input class="menu-item-data-db-id" type="hidden" name="menu-item-db-id[<?php echo $item_id; ?>]" value="<?php echo $item_id; ?>" />
                <input class="menu-item-data-object-id" type="hidden" name="menu-item-object-id[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->object_id ); ?>" />
                <input class="menu-item-data-object" type="hidden" name="menu-item-object[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->object ); ?>" />
                <input class="menu-item-data-parent-id" type="hidden" name="menu-item-parent-id[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->menu_item_parent ); ?>" />
                <input class="menu-item-data-position" type="hidden" name="menu-item-position[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->menu_order ); ?>" />
                <input class="menu-item-data-type" type="hidden" name="menu-item-type[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->type ); ?>" />
            </div><!-- .menu-item-settings-->
            <ul class="menu-item-transport"></ul>
        <?php
        $output .= ob_get_clean();
    }


    function wp_get_nav_menu_items( $menu, $args = array() ) {
    $menu = wp_get_nav_menu_object( $menu );

    if ( ! $menu )
        return false;

    static $fetched = array();

    $items = get_objects_in_term( $menu->term_id, 'nav_menu' );

    if ( empty( $items ) )
        return $items;

    $defaults = array( 'order' => 'ASC', 'orderby' => 'menu_order', 'post_type' => 'nav_menu_item',
        'post_status' => 'publish', 'output' => ARRAY_A, 'output_key' => 'menu_order', 'nopaging' => true );
    $args = wp_parse_args( $args, $defaults );
    if ( count( $items ) > 1 )
        $args['include'] = implode( ',', $items );
    else
        $args['include'] = $items[0];

    $items = get_posts( $args );

    if ( is_wp_error( $items ) || ! is_array( $items ) )
        return false;

    // Get all posts and terms at once to prime the caches
    if ( empty( $fetched[$menu->term_id] ) || wp_using_ext_object_cache() ) {
        $fetched[$menu->term_id] = true;
        $posts = array();
        $terms = array();
        foreach ( $items as $item ) {
            $object_id = get_post_meta( $item->ID, '_menu_item_object_id', true );
            $object    = get_post_meta( $item->ID, '_menu_item_object',    true );
            $type      = get_post_meta( $item->ID, '_menu_item_type',      true );

            if ( 'post_type' == $type )
                $posts[$object][] = $object_id;
            elseif ( 'taxonomy' == $type)
                $terms[$object][] = $object_id;
        }

        if ( ! empty( $posts ) ) {
            foreach ( array_keys($posts) as $post_type ) {
                get_posts( array('post__in' => $posts[$post_type], 'post_type' => $post_type, 'nopaging' => true, 'update_post_term_cache' => false) );
            }
        }
        unset($posts);

        if ( ! empty( $terms ) ) {
            foreach ( array_keys($terms) as $taxonomy ) {
                get_terms($taxonomy, array('include' => $terms[$taxonomy]) );
            }
        }
        unset($terms);
    }

    $items = array_map( 'wp_setup_nav_menu_item', $items );

    if ( ! is_admin() ) // Remove invalid items only in frontend
        $items = array_filter( $items, '_is_valid_nav_menu_item' );

    if ( ARRAY_A == $args['output'] ) {
        $GLOBALS['_menu_item_sort_prop'] = $args['output_key'];
        usort($items, '_sort_nav_menu_items');
        $i = 1;
        foreach( $items as $k => $item ) {
            $items[$k]->$args['output_key'] = $i++;
        }
    }

    return apply_filters( 'wp_get_nav_menu_items',  $items, $menu, $args );
}

} // Walker_Nav_Menu_Edit


class aderna_megamenu_custom_menu {

    /*--------------------------------------------*
     * Constructor
     *--------------------------------------------*/

    /**
     * Initializes the plugin by setting localization, filters, and administration functions.
     */
    function __construct() {

        // add custom menu fields to menu
        add_filter( 'wp_setup_nav_menu_item', array( $this, 'aderna_megamenu_add_custom_nav_fields' ) );
        // save menu custom fields
        add_action( 'wp_update_nav_menu_item', array( $this, 'aderna_megamenu_update_custom_nav_fields'), 10, 3 );
        // edit menu walker
        add_filter( 'wp_edit_nav_menu_walker', array( $this, 'aderna_megamenu_edit_walker'), 10, 3 );


    } // end constructor

    /* All functions will be placed here */

    /**
     * Add custom fields to $item nav object
     * in order to be used in custom Walker
     *
     * @access      public
     * @since       1.0
     * @return      void
    */
    function aderna_megamenu_add_custom_nav_fields( $menu_item ) {

        $menu_item->subtitle = get_post_meta( $menu_item->ID, '_menu_item_subtitle', true );
        $menu_item->mega_title = get_post_meta( $menu_item->ID, '_menu_item_mega_title', true );
        $menu_item->mega_content = get_post_meta( $menu_item->ID, '_menu_item_mega_content', true );
        $menu_item->megamenu_widget_area = get_post_meta( $menu_item->ID, '_menu_item_megamenu_widget_area', true );
        $menu_item->icon = get_post_meta( $menu_item->ID, '_menu_item_icon', true );
        $menu_item->image = get_post_meta( $menu_item->ID, '_menu_item_image', true );
        $menu_item->megashortcode = get_post_meta( $menu_item->ID, '_menu_item_megashortcode', true );
        $menu_item->megamenu_post = get_post_meta( $menu_item->ID, '_menu_item_megamenu_post', true );
        $menu_item->megamenu = get_post_meta( $menu_item->ID, '_menu_item_megamenu', true );
        $menu_item->column = get_post_meta( $menu_item->ID, '_menu_item_column', true );
        $menu_item->column_small = get_post_meta( $menu_item->ID, '_menu_item_column_small', true );
        $menu_item->megatype = get_post_meta( $menu_item->ID, '_menu_item_megatype', true );
        $menu_item->mega_show_title = get_post_meta( $menu_item->ID, '_menu_item_mega_show_title', true );
        $menu_item->megarow = get_post_meta( $menu_item->ID, '_menu_item_megarow', true );
        $menu_item->nav_label = get_post_meta( $menu_item->ID, '_menu_item_nav_label', true );
        return $menu_item;

    }

    /**
     * Save menu custom fields
     *
     * @access      public
     * @since       1.0
     * @return      void
    */
    function aderna_megamenu_update_custom_nav_fields( $menu_id, $menu_item_db_id, $args ) {

        // Check if element is properly sent
        if ( isset( $_REQUEST['menu-item-icon']) && is_array( $_REQUEST['menu-item-icon']) ) {
            $icon_value = $_REQUEST['menu-item-icon'][$menu_item_db_id];
            update_post_meta( $menu_item_db_id, '_menu_item_icon', $icon_value );
        }
        // Check if element is properly sent
        if ( isset( $_REQUEST['menu-item-megamenu-post']) && is_array( $_REQUEST['menu-item-megamenu-post']) ) {
            $megamenu_post_value = $_REQUEST['menu-item-megamenu-post'][$menu_item_db_id];
            update_post_meta( $menu_item_db_id, '_menu_item_megamenu_post', $megamenu_post_value );
        }
        // Check if element is properly sent
        if ( isset( $_REQUEST['menu-item-megamenu-widget-area']) && is_array( $_REQUEST['menu-item-megamenu-widget-area']) ) {
            $megamenu_widget_area_value = $_REQUEST['menu-item-megamenu-widget-area'][$menu_item_db_id];
            update_post_meta( $menu_item_db_id, '_menu_item_megamenu_widget_area', $megamenu_widget_area_value );
        }
        // Check if element is properly sent
        if ( isset( $_REQUEST['menu-item-image']) && is_array( $_REQUEST['menu-item-image']) ) {
            $image_value = $_REQUEST['menu-item-image'][$menu_item_db_id];
            update_post_meta( $menu_item_db_id, '_menu_item_image', $image_value );
        }
        // Check if element is properly sent

        if ( isset( $_REQUEST['menu-item-megashortcode']) && is_array( $_REQUEST['menu-item-megashortcode']) ) {
            $megashortcode_value = $_REQUEST['menu-item-megashortcode'][$menu_item_db_id];
            update_post_meta( $menu_item_db_id, '_menu_item_megashortcode', $megashortcode_value );
        }
        // Check if element is properly sent


        if ( isset( $_REQUEST['menu-item-subtitle']) && is_array( $_REQUEST['menu-item-subtitle']) ) {
            $subtitle_value = $_REQUEST['menu-item-subtitle'][$menu_item_db_id];
            update_post_meta( $menu_item_db_id, '_menu_item_subtitle', $subtitle_value );
        }
         // Check if element is properly sent
        if ( isset( $_REQUEST['menu-item-mega-title']) && is_array( $_REQUEST['menu-item-mega-title']) ) {
            $mega_title_value = $_REQUEST['menu-item-mega-title'][$menu_item_db_id];
            update_post_meta( $menu_item_db_id, '_menu_item_mega_title', $mega_title_value );
        }
         // Check if element is properly sent
        if ( isset( $_REQUEST['menu-item-mega-content']) && is_array( $_REQUEST['menu-item-mega-content']) ) {
            $mega_content_value = $_REQUEST['menu-item-mega-content'][$menu_item_db_id];
            update_post_meta( $menu_item_db_id, '_menu_item_mega_content', $mega_content_value );
        }
         // Check if element is properly sent
        if ( isset( $_REQUEST['menu-item-megamenu']) && is_array( $_REQUEST['menu-item-megamenu']) ) {
            $megamenu_value = isset($_REQUEST['menu-item-megamenu'][$menu_item_db_id])? $_REQUEST['menu-item-megamenu'][$menu_item_db_id] : 0;
            update_post_meta( $menu_item_db_id, '_menu_item_megamenu', $megamenu_value );
        }

         // Check if element is properly sent
        if ( isset( $_REQUEST['menu-item-column']) && is_array( $_REQUEST['menu-item-column']) ) {
            $megamenu_value = isset($_REQUEST['menu-item-column'][$menu_item_db_id])? $_REQUEST['menu-item-column'][$menu_item_db_id] : 0;
            update_post_meta( $menu_item_db_id, '_menu_item_column', $megamenu_value );
        }

         // Check if element is properly sent
        if ( isset( $_REQUEST['menu-item-column-small']) && is_array( $_REQUEST['menu-item-column-small']) ) {
            $megamenu_value = isset($_REQUEST['menu-item-column-small'][$menu_item_db_id])? $_REQUEST['menu-item-column-small'][$menu_item_db_id] : 0;
            update_post_meta( $menu_item_db_id, '_menu_item_column_small', $megamenu_value );
        }

        // Check if element is properly sent
        if ( isset( $_REQUEST['menu-item-megarow']) && is_array( $_REQUEST['menu-item-megarow']) ) {
            $megamenu_value = isset($_REQUEST['menu-item-megarow'][$menu_item_db_id])? $_REQUEST['menu-item-megarow'][$menu_item_db_id] : 0;
            update_post_meta( $menu_item_db_id, '_menu_item_megarow', $megamenu_value );
        }

         // Check if element is properly sent
        if ( isset( $_REQUEST['menu-item-megatype']) && is_array( $_REQUEST['menu-item-megatype']) ) {
            $megamenu_value = isset($_REQUEST['menu-item-megatype'][$menu_item_db_id])? $_REQUEST['menu-item-megatype'][$menu_item_db_id] : 0;
            update_post_meta( $menu_item_db_id, '_menu_item_megatype', $megamenu_value );
        }

        if ( isset( $_REQUEST['menu-item-mega-show-title']) && is_array( $_REQUEST['menu-item-mega-show-title']) ) {
            $megamenu_value = isset($_REQUEST['menu-item-mega-show-title'][$menu_item_db_id])? $_REQUEST['menu-item-mega-show-title'][$menu_item_db_id] : 0;
            update_post_meta( $menu_item_db_id, '_menu_item_mega_show_title', $megamenu_value );
        }

         // Check if element is properly sent
        if ( isset( $_REQUEST['menu-item-nav_label']) && is_array( $_REQUEST['menu-item-nav_label']) ) {
            $megamenu_value = isset($_REQUEST['menu-item-nav_label'][$menu_item_db_id])? $_REQUEST['menu-item-nav_label'][$menu_item_db_id] : 1;
            update_post_meta( $menu_item_db_id, '_menu_item_nav_label', $megamenu_value );
        }


    }

    /**
     * Define new Walker edit
     *
     * @access      public
     * @since       1.0
     * @return      void
    */
    function aderna_megamenu_edit_walker($walker,$menu_id) {

        return 'Walker_Nav_Menu_Edit_Custom';

    }



}

// instantiate plugin's class
$GLOBALS['aderna_custom_menu'] = new aderna_megamenu_custom_menu();
