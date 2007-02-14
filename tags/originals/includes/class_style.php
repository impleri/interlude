<?php
/***************************************************************************
 *							class_style.php
 *							----------------
 *	begin		: 1 January 2006
 *	copyright	: impleri
 *	email		: impleri@impleri.net
 *
 *	Version		: 0.0.1 - 01/01/2006
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

defined( '_IN_SYS' ) or die( 'Direct Access to this location is not allowed.' );

/*
 * Config class
 * ------------
 * Config Data
 *
 * Functions: config, read
 */

class style
{
          var $_data = array();
          var $_files = array();
          var $_mains = array();
          var $compiled_code = array();
          var $template_root;
          var $cache_root;
          var $_check;
          var $main_name;
          var $main_root;
          var $main_prefix;
          var $alt_name;
          var $alt_root;
          var $alt_prefix;
          var $_debug;

          function style($root='.', $alt_template_name='', $template_path='')
          {
                    global $config;

                    $this->cache_root = ($config->data['cache_path']) ? $config->data['cache_path'] : 'cache/';
                    $this->_check = ($config->data['cache_style']) ? false : true;
                    $this->template_root = empty($template_path) ? 'templates/' : $template_path;

                    $this->set($root, $alt_template_name);

                    $this->_debug = false;
          }

          function set($root, $alt_template_name)
          {
                    global $config;

                    $this->root_template = $config->root . $this->template_path;

                    // get main template settings
                    $this->template_name = str_replace('//', '/', str_replace('./', '', substr($root, strlen($this->root_template))) . '/');
                    $this->root = $this->root_template . $this->template_name;
                    $this->cacheprefix = $config->root . $this->cache_path . 'tpl_' . str_replace('/', '_', $this->template_name);

                    // get custom tpls settings
                    $this->alt_template_name = $this->tpl_realpath($alt_template_name);
                    $this->alt_root = empty($this->alt_template_name) ? '' : $this->root_template . $this->alt_template_name;
                    $this->alt_cacheprefix = empty($this->alt_template_name) ? '' : $config->root . $this->cache_path . 'tpl_' . str_replace('/', '_', $this->alt_template_name);

                    // raz
                    $this->_tpldata = array();
          }

          function tpl_realpath($tpl_name)
          {
                    global $config;

                    if ( !empty($tpl_name) )
                    {
                              $real_path = phpbb_realpath($this->root);
                              if ( $real_path != $this->root)
                              {
                                        $tpl_real_path = phpbb_realpath($this->root . $tpl_name);
                                        if ( empty($tpl_real_path) )
                                        {
                                                  $tpl_name = '';
                                        }
                                        else
                                        {
                                                  $root_real_path = phpbb_realpath($this->root_template);
                                                  $tpl_name = str_replace('//', '/', str_replace('\\', '/', substr($tpl_real_path, strlen($root_real_path)+1)) . '/');
                                        }
                              }
                              // phpbb_realpath fails to get the real path sometime (when not available), so find another way
                              else
                              {
                                        $res = $this->template_name;
                                        if ( substr($tpl_name, 0, 2) == './' )
                                        {
                                                  $tpl_name = substr($tpl_name, 2);
                                        }
                                        if ( substr($tpl_name, 0, 3) == '../' )
                                        {
                                                  $res = '';
                                                  $tpl_name = substr($tpl_name, 3);
                                        }
                                        if ( preg_match('/\.\.\//', $tpl_name) )
                                        {
                                                  $tpl_name = '';
                                        }
                                        else
                                        {
                                                  $tpl_name = str_replace('//', '/', str_replace('./', '', $res . $tpl_name) . '/');
                                        }
                              }
                    }
                    return $tpl_name;
          }

          function set_switch($switch_name, $value=true)
          {
                    $this->assign_block_vars($switch_name . ($value ? '' : '_ELSE'), array());
          }

          function save(&$save)
          {
                    $save = $this->_tpldata;
          }

          function destroy()
          {
                    $this->_tpldata = array();
          }

          function restore(&$save)
          {
                    $this->_tpldata = $save;
          }

          function get_pparse($handle)
          {
                    ob_start();
                    $this->pparse($handle);
                    $res = ob_get_contents();
                    ob_end_clean();
                    return $res;
          }

          // Sets the template filenames for handles. $filename_array
          // should be a hash of handle => filename pairs.
          function set_filenames($filename_array)
          {
                    if ( !is_array($filename_array) )
                    {
                              return false;
                    }

                    $template_names = '';
                    foreach ($filename_array as $handle => $filename)
                    {
                              if ( empty($filename) )
                              {
                                        message_die(GENERAL_ERROR, 'template error - Empty filename specified for ' . $handle, '', __LINE__, __FILE__);
                              }

                              $this->filename[$handle] = $filename;
                              if ( !empty($this->alt_root) )
                              {
                                        $this->files[$handle] = $this->alt_root . $filename;
                              }

                              // doesn't exists : try the main
                              if ( !$this->mains[$handle] = (!empty($this->alt_root) && file_exists($this->files[$handle])) )
                              {
                                        $this->files[$handle] = $this->root . $filename;
                                        $this->mains[$handle] = false;
                              }
                    }

                    return true;
          }

          function make_filename($filename)
          {
                    return !empty($this->alt_root) && file_exists($this->alt_root . $filename) ?  $this->alt_root . $filename : (file_exists($this->root . $filename) ? $this->root . $filename : '');
          }

          // Methods for loading and evaluating the templates
          function pparse($handle)
          {
                    global $user;
                    $this->no_debug = $this->no_debug || !is_object($user) || ($user->data['user_level'] != ADMIN);

                    if ( defined('DEBUG_TEMPLATE') && !$this->no_debug )
                    {
                              echo '<!-- Start of : ' . $this->files[$handle] . ' :: ' . $handle . ' -->' . "\n";
                    }
                    if ($filename = $this->_tpl_load($handle))
                    {
                              include($filename);
                    }
                    else
                    {
                              eval(' ?>' . $this->compiled_code[$handle] . '<?php ');
                    }
                    if ( defined('DEBUG_TEMPLATE') && !$this->no_debug )
                    {
                              echo '<!-- End of : ' . $this->files[$handle] . ' :: ' . $handle . ' -->' . "\n";
                    }

                    return true;
          }

          function assign_var_from_handle($varname, $handle)
          {
                    return $this->assign_vars(array($varname => $this->get_pparse($handle)));
          }

          // Load a compiled template if possible, if not, recompile it
          function _tpl_load($handle)
          {
                    global $config, $user, $db;

                    // If we don't have a file assigned to this handle, die.
                    if ( !isset($this->files[$handle]) )
                    {
                              message_die(GENERAL_ERROR, 'template->_tpl_load(): No file specified for handle ' . $handle, '', __LINE__, __FILE__);
                    }

                    // get the file name
                    $w_filename = str_replace('/', '_', $this->filename[$handle]);
                    $filename = ($this->mains[$handle] ? $this->alt_cacheprefix : $this->cacheprefix) . $w_filename . '.' . $config->ext;

                    // Recompile page if the original template is newer, otherwise load the compiled version
                    if ( !empty($this->compiled_code[$handle]) )
                    {
                              return false;
                    }
                    else if ( $config->data['cache_style'] && @file_exists($filename) && (!$this->_check || (@filemtime($filename) > @filemtime($this->files[$handle]))) )
                    {
                              return $filename;
                    }
                    else
                    {
                              $this->_tpl_load_file($handle);
                    }
                    return false;
          }

          // Load template source from file
          function _tpl_load_file($handle)
          {
                    global $config;

                    // Try and open template for read
                    if (!($fp = @fopen($this->files[$handle], 'r')))
                    {
                              message_die(GENERAL_ERROR, 'template->_tpl_load(): File ' . $this->files[$handle] . ' does not exist or is empty', '', __LINE__, __FILE__);
                    }

                    // compile required
                    include_once($config->url('includes/class_compiler'));
                    $compiler = new compiler();
                    $this->compiled_code[$handle] = $compiler->compile(trim(@fread($fp, filesize($this->files[$handle]))));
                    @fclose($fp);

                    // output the template to the cache
                    if ( $config->data['cache_template'] )
                    {
                              $filename = ($this->mains[$handle] ? $this->alt_cacheprefix : $this->cacheprefix) . str_replace('/', '_', $this->filename[$handle]) . '.' . $config->ext;
                              $compiler->compile_write($handle, $this->compiled_code[$handle], $filename);
                    }
                    unset($compiler);
          }

          // Assign key variable pairs from an array
          function assign_vars($vararray)
          {
                    $this->_tpldata['.'][0] = array_merge(empty($this->_tpldata['.'][0]) ? array() : $this->_tpldata['.'][0], $vararray);
                    return true;
          }

          // Assign key variable pairs from an array to a specified block
          function assign_block_vars($blockname, $vararray)
          {
                    if (strstr($blockname, '.'))
                    {
                              // Nested block.
                              $blocks = explode('.', $blockname);
                              $blockcount = sizeof($blocks) - 1;

                              $str = &$this->_tpldata; 
                              for ($i = 0; $i < $blockcount; $i++) 
                              {
                                        $str = &$str[$blocks[$i]]; 
                                        $str = &$str[sizeof($str) - 1]; 
                              }
                              $str[$blocks[$blockcount]][] = $vararray;
                    }
                    else
                    {
                              $this->_tpldata[$blockname][] = $vararray;
                    }

                    return true;
          }

          function assign_lastblock_vars($blockname, $vararray)
          {
                    if ( strstr($blockname, '.') )
                    {
                              $blocks = explode('.', $blockname);
                              $blockcount = sizeof($blocks);

                              $str = &$this->_tpldata; 
                              for ($i = 0; $i < $blockcount; $i++) 
                              {
                                        $str = &$str[ $blocks[$i] ];
                                        $str = &$str[ sizeof($str) - 1 ];
                              }
                    }
                    else
                    {
                              $str = &$this->_tpldata[$blockname];
                              $str = &$str[ sizeof($str) - 1 ];
                    }
                    $str = array_merge($str, $vararray);
                    return true;
          }

          function unset_block_vars($blockname)
          {
                    // find the block (last iteration)
                    if ( strstr($blockname, '.') )
                    {
                              $blocks = explode('.', $blockname);
                              $blockcount = sizeof($blocks) - 1;

                              $str = &$this->_tpldata; 
                              for ($i = 0; $i < $blockcount; $i++) 
                              {
                                        $str = &$str[ $blocks[$i] ];
                                        $str = &$str[ sizeof($str) - 1 ];
                              }
                              if ( isset($str[ $blocks[$blockcount] ]) )
                              {
                                        unset($str[ $blocks[$blockcount] ]);
                                        return true;
                              }
                    }
                    else
                    {
                              if ( isset($this->_tpldata[$blockname]) )
                              {
                                        unset($this->_tpldata[$blockname]);
                                        return true;
                              }
                    }
                    return false;
          }

          // Include a seperate template
          function _tpl_include($filename)
          {
                    if ( !empty($filename) )
                    {
                              $this->set_filenames(array($filename => $filename));
                              $this->pparse($filename);
                    }
          }
}

?>