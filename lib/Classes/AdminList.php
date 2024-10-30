<?php
/***/

namespace Cadesign\NataliApi;

use Bitrix\Main\Engine\Controller;
use Cadesign\NataliApi\CategoryList;
use Cadesign\NataliApi\ProductList;


class AdminList extends \CAdminList
{

    public function Display()
    {
        /** @global CMain $APPLICATION */
        global $APPLICATION;

        foreach(GetModuleEvents("main", "OnAdminListDisplay", true) as $arEvent)
            ExecuteModuleEventEx($arEvent, [&$this]);

        $errmsg = '';
        foreach($this->arFilterErrors as $err)
            $errmsg .= ($errmsg <> '' ? '<br>' : '') . $err;
        foreach($this->arUpdateErrors as $err)
            $errmsg .= ($errmsg <> '' ? '<br>' : '') . $err[0];
        foreach($this->arGroupErrors as $err)
            $errmsg .= ($errmsg <> '' ? '<br>' : '') . $err[0];
        if($errmsg <> '')
            CAdminMessage::ShowMessage(["MESSAGE" => GetMessage("admin_lib_error"), "DETAILS" => $errmsg, "TYPE" => "ERROR"]);

        $successMessage = '';
        for($i = 0, $cnt = count($this->arActionSuccess); $i < $cnt; $i++)
            $successMessage .= ($successMessage != '' ? '<br>' : '') . $this->arActionSuccess[$i];
        if($successMessage != '')
            CAdminMessage::ShowMessage(["MESSAGE" => GetMessage("admin_lib_success"), "DETAILS" => $successMessage, "TYPE" => "OK"]);

        echo $this->sPrologContent;

        if($this->sContent === false)
        {
            ?>
            <div class="adm-list-table-wrap<?php echo $this->context ? '' : ' adm-list-table-without-header' ?><?php echo count($this->arActions) <= 0 && !$this->bCanBeEdited ? ' adm-list-table-without-footer' : '' ?>">
            <?
        }

        if($this->context)
            $this->context->Show();

        if($this->bEditMode && !$this->bCanBeEdited)
            $this->bEditMode = false;

        if($this->sContent !== false)
        {
            echo $this->sContent;

            return;
        }

        $bShowSelectAll = (count($this->arActions) > 0 || $this->bCanBeEdited);
        $this->bShowActions = false;
        foreach($this->aRows as $row)
        {
            if(!empty($row->aActions))
            {
                $this->bShowActions = true;
                break;
            }
        }

        //!!! insert filter's hiddens
        echo bitrix_sessid_post();
        //echo $this->sNavText;

        $colSpan = 0;
        ?>
        <table class="adm-list-table" id="<?php echo $this->table_id; ?>">
            <thead>
            <tr class="adm-list-table-header">
                <?
                if($bShowSelectAll):
                    ?>
                    <td class="adm-list-table-cell adm-list-table-checkbox"
                        onclick="this.firstChild.firstChild.click(); return BX.PreventDefault(event);">
                        <div class="adm-list-table-cell-inner"><input class="adm-checkbox adm-designed-checkbox"
                                                                      type="checkbox"
                                                                      id="<?php echo $this->table_id ?>_check_all"
                                                                      onclick="<?php echo $this->table_id ?>.SelectAllRows(this); return BX.eventCancelBubble(event);"
                                                                      title="<?php echo GetMessage("admin_lib_list_check_all") ?>"/><label
                                    for="<?php echo $this->table_id ?>_check_all" class="adm-designed-checkbox-label"></label>
                        </div>
                    </td>
                    <?
                    $colSpan++;
                endif;

                if($this->bShowActions):
                    ?>
                    <td class="adm-list-table-cell adm-list-table-popup-block"
                        title="<?php echo GetMessage("admin_lib_list_act") ?>">
                        <div class="adm-list-table-cell-inner"></div>
                    </td>
                    <?
                    $colSpan++;
                endif;

                foreach($this->aVisibleHeaders as $header):
                    $bSort = $this->sort && !empty($header["sort"]);

                    if($bSort)
                        $attrs = $this->sort->Show($header["content"], $header["sort"], $header["title"], "adm-list-table-cell");
                    else
                        $attrs = 'class="adm-list-table-cell"';

                    ?>
                    <td <?php echo $attrs ?>>
                        <div class="adm-list-table-cell-inner"><?php echo $header["content"] ?></div>
                    </td>
                    <?
                    $colSpan++;
                endforeach;
                ?>
            </tr>
            </thead>
            <tbody>
            <?
            if(!empty($this->aRows)):
                foreach($this->aRows as $row)
                {
                    $row->Display();
                }
            elseif(!empty($this->aHeaders)):
                ?>
                <tr>
                    <td colspan="<?php echo $colSpan ?>"
                        class="adm-list-table-cell adm-list-table-empty"><?php echo GetMessage("admin_lib_no_data") ?></td>
                </tr>
            <?
            endif;
            ?>
            </tbody>
        </table>
        <?
        $this->ShowActionTable();

// close form and div.adm-list-table-wrap

        echo $this->sEpilogContent;
        echo '

</div>
';
        echo $this->sNavText;
    }


}