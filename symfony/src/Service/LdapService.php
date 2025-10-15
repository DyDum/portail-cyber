<?php
namespace App\Service;

use Symfony\Component\Ldap\Ldap;

class LdapService
{
    private Ldap $ldap;
    private string $baseDn;

    public function __construct()
    {
        $this->ldap = Ldap::create('ext_ldap', [
            'host' => $_ENV['LDAP_SERVER'],
            'port' => 389,
            'encryption' => 'none',
            'options' => ['protocol_version' => 3],
        ]);

        $this->ldap->bind($_ENV['LDAP_BIND_DN'], $_ENV['LDAP_BIND_PASSWORD']);
        $this->baseDn = $_ENV['LDAP_BASE_DN'];
    }

    public function search(string $filter): array
    {
        $query = $this->ldap->query($this->baseDn, $filter);
        return iterator_to_array($query->execute());
    }
}
