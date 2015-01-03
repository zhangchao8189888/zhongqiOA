<?php
/**
 * 工资dao
 * @author zhang.chao
 *
 */
class SalaryDao extends BaseDao {

    /**
     *
     * @return BaseConfigDao
     */
    function SalaryDao() {
        parent::BaseDao ();
    }
    function saveFukuanTongzhi($fukuan) {
        $sql="insert into OA_fukuantongzhi
        (company_name,salary_time_id,salary_time,yingfu_money,
        laowufei_money,fapiao_id_json,jieshou_person_id,jieshou_person_name,zhifu_status,more)
        values ('{$fukuan['company_name']}',{$fukuan['salary_time_id']},'{$fukuan['salary_time']}',
        '{$fukuan['salary_time']}',{$fukuan['yingfu_money']},{$fukuan['laowufei_money']},'{$fukuan['fapiao_id_json']}',
        {$fukuan['jieshou_person_id']},'{$fukuan['jieshou_person_name']}',0,'{$fukuan['more']}'
        )";
        $result=$this->g_db_query($sql);
        return $result;
    }
}
?>
