<?php
class Intraface_shared_filehandler_Append_File extends Doctrine_Record
{
    function setTableDefinition()
    {
        $this->setTableName('filehandler_append_file');
        $this->hasColumn('intranet_id',    'integer', 11);
        $this->hasColumn('date_created',    'datetime');
        $this->hasColumn('date_updated',    'datetime');
        $this->hasColumn('belong_to_key',   'integer', 11);
        $this->hasColumn('belong_to_id',    'integer', 11);
        $this->hasColumn('file_handler_id', 'integer', 11);
        $this->hasColumn('description',     'string', 65555);
        $this->hasColumn('position',        'integer', 11);
        $this->hasColumn('active',          'integer', 1);
    }

    public function setUp()
    {
        // @todo should add the template for intranet
        //$this->actAs('Intraface_Doctrine_Template_Intranet');
        $options = array('created' =>  array('name'          => 'date_created',    // Name of created column
                                             'type'          => 'timestamp',     // Doctrine column data type
                                             'options'       => array(),         // Array of options for column
                                             'format'        => 'Y-m-d H:i:s',   // Format of date used with PHP date() function(default)
                                             'disabled'      => false,           // Disable the created column(default)
                                             'expression'    => 'NOW()'),        // Update column with database expression(default=false)
                         'updated' =>  array('name'          => 'date_updated',    // Name of updated column(default)
                                             'type'          => 'timestamp',     // Doctrine column data type(default)
                                             'options'       => array(),         // Array of options for column(default)
                                             'format'        => 'Y-m-d H:i:s',   // Format of date used with PHP date() function(default)
                                             'disabled'      => false,           // Disable the updated column(default)
                                             'expression'    => 'NOW()',         // Use a database expression to set column(default=false)
                                             'onInsert'      => true));          // Whether or not to set column onInsert(default)

        $this->actAs('Timestampable', $options);

        $options['extra_where'] = array('intranet_id', 'belong_to_key', 'belong_to_id');

        $this->actAs('Positionable', $options);
    }

    function getIntranetId()
    {
        return $this->intranet_id;
    }
}