<?php

namespace Dhl\Versenden\Bcs\Soap;

class ServiceconfigurationDetails
{

    /**
     * @var anonymous153 $active
     */
    protected $active = null;

    /**
     * @var anonymous154 $details
     */
    protected $details = null;

    /**
     * @param anonymous153 $active
     * @param anonymous154 $details
     */
    public function __construct($active, $details)
    {
      $this->active = $active;
      $this->details = $details;
    }

    /**
     * @return anonymous153
     */
    public function getActive()
    {
      return $this->active;
    }

    /**
     * @param anonymous153 $active
     * @return \Dhl\Versenden\Bcs\Soap\ServiceconfigurationDetails
     */
    public function setActive($active)
    {
      $this->active = $active;
      return $this;
    }

    /**
     * @return anonymous154
     */
    public function getDetails()
    {
      return $this->details;
    }

    /**
     * @param anonymous154 $details
     * @return \Dhl\Versenden\Bcs\Soap\ServiceconfigurationDetails
     */
    public function setDetails($details)
    {
      $this->details = $details;
      return $this;
    }

}
