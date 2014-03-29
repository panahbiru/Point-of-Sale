<?php
class Invoice extends CI_Model{
    function __construct() {
        parent::__construct();
    }
    function get($end,$start,$like,$branch){
                $this->db->select('purchase_invoice.*,direct_grn.grn_no,purchase_invoice.invoice,purchase_invoice.guid as invoice_guid, purchase_invoice.date as date,suppliers.first_name as s_name,suppliers.company_name as c_name');
                $this->db->from('purchase_invoice')->where('purchase_invoice.branch_id',$branch)->where('po','non');
                $this->db->join('direct_grn', 'direct_grn.guid=purchase_invoice.grn','left');
                $this->db->join('suppliers', 'suppliers.guid=direct_grn.supplier_id AND direct_grn.guid=purchase_invoice.grn','left');
                $this->db->limit($end,$start); 
                $this->db->or_like($like);     
                $query=$this->db->get();
                $data=array();
                foreach ($query->result_array() as $row){
                 
                    $row['date']=date('d-m-Y',$row['date']);
                    $data[]=$row;
                   
                }
                $this->db->select('purchase_invoice.*,grn.grn_no,purchase_invoice.invoice,purchase_invoice.guid as invoice_guid, purchase_invoice.date as date,suppliers.first_name as s_name,suppliers.company_name as c_name');
                $this->db->from('purchase_invoice')->where('purchase_invoice.branch_id',$branch)->where('purchase_invoice.po <>','non');
                $this->db->join('grn', 'grn.guid=purchase_invoice.grn','left');
                $this->db->join('purchase_order', 'grn.po=purchase_order.guid','left');
                $this->db->join('suppliers', 'suppliers.guid=purchase_order.supplier_id AND grn.guid=purchase_invoice.grn','left');
                $this->db->limit($end,$start); 
                $this->db->or_like($like);     
                $query=$this->db->get();
               
                foreach ($query->result_array() as $row){
                 
                    $row['date']=date('d-m-Y',$row['date']);
                    $data[]=$row;
                   
                }
                
                
                
                return $data; 
        
    }
    function search_grn_order($like,$branch){
        $this->db->select('direct_grn.*,suppliers.guid as s_guid,suppliers.first_name as s_name,suppliers.company_name as c_name');
        $this->db->from('direct_grn')->where('direct_grn.branch_id',$branch)->where('direct_grn.order_status',1)->where('direct_grn.active_status',1)->where('direct_grn.delete_status',0);
        $or_like=array('grn_no'=>$like,'suppliers.company_name'=>$like,'suppliers.first_name'=>$like);
        $this->db->join('suppliers', 'suppliers.guid=direct_grn.supplier_id ','left');

        $this->db->or_like($or_like);     
        $sql=$this->db->get();
        $data=array();
        foreach($sql->result_array() as $row){
            $row['grn_date']=date('d-m-Y',$row['grn_date']);
          
             $data[]=$row;

        }
        
        // get data from grn
        
        $this->db->select('grn.guid,grn.grn_no,grn.date as grn_date ,grn.po,purchase_order.supplier_id,suppliers.guid as s_guid');
        $this->db->from('grn')->where('grn.branch_id',$branch)->where('grn.grn_status',1)->where('grn.delete_status',0);
        $or_like=array('grn_no'=>$like,'suppliers.company_name'=>$like,'suppliers.first_name'=>$like);
        $this->db->join('purchase_order', 'purchase_order.guid=grn.po ','left');
        $this->db->join('suppliers', 'suppliers.guid=purchase_order.supplier_id ','left');
        $this->db->or_like($or_like);     
        $sql=$this->db->get();
       
        foreach($sql->result_array() as $row){
            $row['grn_date']=date('d-m-Y',$row['grn_date']);
            $data[]=$row;
        }
         return $data;
               
      


    }
   
  
  
   
    
    function count($branch){
        $this->db->select()->from('purchase_invoice')->where('branch_id',$branch);
        $sql=  $this->db->get();
        return $sql->num_rows();
    }
    
   
      function get_direct_grn($guid){
         $this->db->select('items.tax_Inclusive ,tax_types.type as tax_type_name,taxes.value as tax_value,taxes.type as tax_type,suppliers_x_items.quty as item_limit,suppliers.guid as s_guid,suppliers.first_name as s_name,suppliers.company_name as c_name,suppliers.address1 as address,direct_grn.*,direct_grn_items.discount_per as dis_per ,direct_grn_items.discount_amount as item_dis_amt ,direct_grn_items.tax as dis_amt ,direct_grn_items.tax as order_tax,direct_grn_items.item ,direct_grn_items.quty ,direct_grn_items.free ,direct_grn_items.cost ,direct_grn_items.sell ,direct_grn_items.mrp,direct_grn_items.guid as o_i_guid ,direct_grn_items.amount ,direct_grn_items.date,items.guid as i_guid,items.name as items_name,items.code as i_code')->from('direct_grn')->where('direct_grn.guid',$guid);
         $this->db->join('direct_grn_items', 'direct_grn_items.order_id = direct_grn.guid AND direct_grn_items.delete_status=0','left');
         $this->db->join('items', "items.guid=direct_grn_items.item AND direct_grn_items.order_id='".$guid."' AND direct_grn_items.delete_status=0",'left');
         $this->db->join('taxes', "items.tax_id=taxes.guid AND items.guid=direct_grn_items.item  AND direct_grn_items.delete_status=0",'left');
         $this->db->join('tax_types', "taxes.type=tax_types.guid AND items.tax_id=taxes.guid AND items.guid=direct_grn_items.item  AND direct_grn_items.delete_status=0",'left');
         $this->db->join('suppliers', "suppliers.guid=direct_grn.supplier_id AND direct_grn_items.order_id='".$guid."'  AND direct_grn_items.delete_status=0",'left');
         $this->db->join('suppliers_x_items', "suppliers_x_items.supplier_id=direct_grn.supplier_id AND suppliers_x_items.item_id=direct_grn_items.item AND direct_grn_items.order_id='".$guid."'  AND direct_grn_items.delete_status=0",'left');
         $sql=  $this->db->get();
         $data=array();
         foreach($sql->result_array() as $row){
             
          $row['grn_date']=date('d-m-Y',$row['grn_date']);
       
      
         
          $data[]=$row;
         }
         return $data;
    }
    function get_goods_receiving_note($guid){
        $this->db->select('grn.date as grn_date,grn.note as grn_note,grn.remark as grn_remark,grn.grn_no,grn_x_items.guid as grn_items_guid,grn_x_items.quty as rece_quty,grn_x_items.free as rece_free,items.tax_Inclusive ,grn.po,tax_types.type as tax_type_name,taxes.value as tax_value,taxes.type as tax_type,suppliers_x_items.quty as item_limit,suppliers.guid as s_guid,suppliers.first_name as s_name,suppliers.company_name as c_name,suppliers.address1 as address,purchase_order.*,purchase_order_items.discount_per as dis_per ,purchase_order_items.discount_amount as item_dis_amt ,purchase_order_items.tax as dis_amt ,purchase_order_items.tax as order_tax,purchase_order_items.item ,purchase_order_items.quty ,purchase_order_items.free,purchase_order_items.guid as o_i_guid ,purchase_order_items.received_quty ,purchase_order_items.received_free ,purchase_order_items.cost ,purchase_order_items.sell ,purchase_order_items.mrp,purchase_order_items.guid as o_i_guid ,purchase_order_items.amount ,purchase_order_items.date,items.guid as i_guid,items.name as items_name,items.code as i_code')->from('grn')->where('grn.guid',$guid);
        $this->db->join('purchase_order', 'grn.po=purchase_order.guid','left');
        $this->db->join('grn_x_items', 'grn_x_items.grn=grn.guid','left');
        $this->db->join('purchase_order_items', 'purchase_order_items.order_id = purchase_order.guid AND grn_x_items.item=purchase_order_items.item AND purchase_order_items.delete_status=0','left');
        $this->db->join('items', "items.guid=purchase_order_items.item AND items.guid=grn_x_items.item AND purchase_order_items.order_id=purchase_order.guid AND purchase_order_items.delete_status=0",'left');
        $this->db->join('taxes', "items.tax_id=taxes.guid AND items.guid=purchase_order_items.item  AND purchase_order_items.delete_status=0",'left');
        $this->db->join('tax_types', "taxes.type=tax_types.guid AND items.tax_id=taxes.guid AND items.guid=purchase_order_items.item  AND purchase_order_items.delete_status=0",'left');
        $this->db->join('suppliers', "suppliers.guid=purchase_order.supplier_id AND purchase_order_items.order_id=purchase_order.guid  AND purchase_order_items.delete_status=0",'left');
        $this->db->join('suppliers_x_items', "suppliers_x_items.supplier_id=purchase_order.supplier_id AND suppliers_x_items.item_id=purchase_order_items.item AND purchase_order_items.order_id='".$guid."'  AND purchase_order_items.delete_status=0",'left');
        $sql=  $this->db->get();
        $data=array();
        foreach($sql->result_array() as $row){             
            $row['po_date']=date('d-m-Y',$row['po_date']);       
            $row['grn_date']=date('d-m-Y',$row['grn_date']);       
            $row['exp_date']=date('d-m-Y',$row['exp_date']);         
            $row['date']=date('d-m-Y',$row['date']);         
            $data[]=$row;
        }
        return $data;
     }
     
    
    
}
?>
