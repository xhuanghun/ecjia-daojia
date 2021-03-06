<?php defined('IN_ECJIA') or exit('No permission resources.'); ?>
<!-- {extends file="ecjia.dwt.php"} -->

<!-- {block name="footer"} -->
<script type="text/javascript">
    ecjia.admin.weapp.init();
</script>
<!-- {/block} -->

<!-- {block name="main_content"} -->
<div>
    <h3 class="heading">
        <!-- {if $ur_here}{$ur_here}{/if} -->
        {if $action_link}
        <a class="btn plus_or_reply data-pjax" href="{$action_link.href}" id="sticky_a"><i class="fontello-icon-reply"></i>{$action_link.text}</a>
        {/if}
    </h3>
</div>

<div class="row-fluid edit-page">
    <div class="span12">
        <div class="tabbable">
            <form class="form-horizontal" action="{$form_action}" method="post" name="theForm" enctype="multipart/form-data">
                <div class="tab-content">
                    <fieldset>
                        <div class="row-fluid edit-page">
                            {if $wxapp_info.id neq ''}
                            <div class="control-group formSep">
                                <label class="control-label">{t domain="weapp"}UUID：{/t}</label>
                                <div class="controls l_h30">
                                    {$wxapp_info.uuid}<br>
                                </div>
                            </div>
                            {/if}
                            <div class="control-group formSep">
                                <label class="control-label">{t domain="weapp"}小程序名称：{/t}</label>
                                <div class="controls">
                                    <input type="text" name="name" value="{$wxapp_info.name}"/>
                                    <span class="input-must">*</span>
                                </div>
                            </div>
                            <div class="control-group formSep">
                                <label class="control-label">{t domain="weapp"}Logo：{/t}</label>
                                <div class="controls chk_radio">
                                    <div class="fileupload {if $wxapp_info.logo}fileupload-exists{else}fileupload-new{/if}" data-provides="fileupload">
                                        <div class="fileupload-preview fileupload-exists thumbnail" style="width: 50px; height: 50px; line-height: 50px;">
                                            {if $wxapp_info.logo}
                                            <img src="{$wxapp_info.logo}" alt='{t domain="weapp"}图片预览{/t}'/>
                                            {/if}
                                        </div>
                                        <span class="btn btn-file">
											<span class="fileupload-new">{t domain="weapp"}浏览{/t}</span>
											<span class="fileupload-exists">{t domain="weapp"}修改{/t}</span>
											<input type='file' name='weapp_logo' size="35"/>
										</span>
                                        <a class="btn fileupload-exists" {if !$wxapp_info.logo}data-dismiss="fileupload" href="javascript:;" {else}data-toggle="ajaxremove" data-msg='{t domain="weapp"}您确定要删除吗？{/t}' href='{url path="weapp/admin/remove_logo" args="id={$wxapp_info.id}"}' title='{t domain="weapp"}删除{/t}' {/if}>{t domain="weapp"}删除{/t}</a>
                                    </div>
                                </div>
                            </div>
                            <div class="control-group formSep">
                                <label class="control-label">{t domain="weapp"}AppID：{/t}</label>
                                <div class="controls">
                                    <input type="text" name="appid" value="{$wxapp_info.appid}"/>
                                    <span class="input-must">*</span>
                                </div>
                            </div>

                            <div class="control-group formSep">
                                <label class="control-label">{t domain="weapp"}AppSecret：{/t}</label>
                                <div class="controls">
                                    <input type="text" name="appsecret" value="{$wxapp_info.appsecret}"/>
                                    <span class="input-must">*</span>
                                </div>
                            </div>

                            <div class="control-group formSep">
                                <label class="control-label">{t domain="weapp"}状态：{/t}</label>
                                <div class="controls chk_radio">
                                    <input type="radio" name="status" value="1" {if $wxapp_info.status eq 1}checked{/if}><span>{t domain="weapp"}开启{/t}</span>
                                    <input type="radio" name="status" value="0" {if $wxapp_info.status eq 0}checked{/if}><span>{t domain="weapp"}关闭{/t}</span>
                                </div>
                            </div>

                            <div class="control-group formSep">
                                <label class="control-label">{t domain="weapp"}排序：{/t}</label>
                                <div class="controls">
                                    <input type="text" name="sort" value="{$wxapp_info.sort|default:0}"/>
                                </div>
                            </div>

                            <div class="control-group">
                                <div class="controls">
                                    {if $wxapp_info.id eq ''}
                                    <input type="submit" name="submit" value='{t domain="weapp"}确定{/t}' class="btn btn-gebo"/>
                                    {else}
                                    <input type="submit" name="submit" value='{t domain="weapp"}更新{/t}' class="btn btn-gebo"/>
                                    {/if}
                                    <input name="id" type="hidden" value="{$wxapp_info.id}">
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- {/block} -->