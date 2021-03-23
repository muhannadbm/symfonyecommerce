<?php

namespace App\Entity\Admin;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Admin\SettingRepository")
 */
class Setting
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $keywords;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $company;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $fax;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $smtpserver;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $smtpemail;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $smptppassword;

    /**
     * @ORM\Column(type="integer")
     */
    private $smtpport;

    /**
     * @ORM\Column(type="text")
     */
    private $aboutus;

    /**
     * @ORM\Column(type="text")
     */
    private $aboutusar;


    /**
     * @ORM\Column(type="text")
     */
    private $aboutustr;


    /**
     * @ORM\Column(type="text")
     */
    private $contact;

    /**
     * @ORM\Column(type="text")
     */
    private $referances;
    
        /**
     * @ORM\Column(type="text")
     */
    private $referancestr;
    /**
     * @ORM\Column(type="text")
     */
    private $referancesar;

    

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $status;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getKeywords(): ?string
    {
        return $this->keywords;
    }

    public function setKeywords(string $keywords): self
    {
        $this->keywords = $keywords;

        return $this;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(string $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getFax(): ?string
    {
        return $this->fax;
    }

    public function setFax(string $fax): self
    {
        $this->fax = $fax;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getSmtpserver(): ?string
    {
        return $this->smtpserver;
    }

    public function setSmtpserver(string $smtpserver): self
    {
        $this->smtpserver = $smtpserver;

        return $this;
    }

    public function getSmtpemail(): ?string
    {
        return $this->smtpemail;
    }

    public function setSmtpemail(string $smtpemail): self
    {
        $this->smtpemail = $smtpemail;

        return $this;
    }

    public function getSmptppassword(): ?string
    {
        return $this->smptppassword;
    }

    public function setSmptppassword(string $smptppassword): self
    {
        $this->smptppassword = $smptppassword;

        return $this;
    }

    public function getSmtpport(): ?int
    {
        return $this->smtpport;
    }

    public function setSmtpport(int $smtpport): self
    {
        $this->smtpport = $smtpport;

        return $this;
    }

    public function getAboutus(): ?string
    {
        return $this->aboutus;
    }

    public function setAboutus(string $aboutus): self
    {
        $this->aboutus = $aboutus;

        return $this;
    }
    
    
        public function getAboutusar(): ?string
    {
        return $this->aboutusar;
    }

    public function setAboutusar(string $aboutusar): self
    {
        $this->aboutusar = $aboutusar;

        return $this;
    }
    
    
    
        public function getAboutustr(): ?string
    {
        return $this->aboutustr;
    }

    public function setAboutustr(string $aboutustr): self
    {
        $this->aboutustr = $aboutustr;

        return $this;
    }
    

    public function getContact(): ?string
    {
        return $this->contact;
    }

    public function setContact(string $contact): self
    {
        $this->contact = $contact;

        return $this;
    }

    public function getReferances(): ?string
    {
        return $this->referances;
    }

    public function setReferances(string $referances): self
    {
        $this->referances = $referances;

        return $this;
    }
    
    
    
        public function getReferancestr(): ?string
    {
        return $this->referancestr;
    }

    public function setReferancestr(string $referancestr): self
    {
        $this->referancestr = $referancestr;

        return $this;
    }
    
    
    
    
        public function getReferancesar(): ?string
    {
        return $this->referancesar;
    }

    public function setReferancesar(string $referancesar): self
    {
        $this->referancesar = $referancesar;

        return $this;
    }
    
    
    

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }
}
