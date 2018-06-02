<?php

namespace Drupal\vault;

class VaultLeaseStorage {

  /**
   * @var \Drupal\Core\KeyValueStore\KeyValueStoreExpirableInterface
   */
  protected $storage;

  /**
   * VaultLeaseStorage constructor.
   */
  public function __construct() {
    $this->storage = \Drupal::keyValueExpirable("vault_lease");
  }

  /**
   * @param $lease_id string
   *  The lease ID.
   *
   * @return mixed
   */
  public function getLease($lease_id) {
    return $this->storage->get($lease_id);
  }

  public function getAllLeases() {
    return $this->storage->getAll();
  }

  /**
   * @param $lease_id string
   *  The lease ID.
   */
  public function deleteLease($lease_id) {
    $this->storage->delete($lease_id);
  }

  /**
   * @param $lease_id string
   *  The lease ID.
   * @param $data
   *  The lease data.
   * @param $expires
   *  The lease expiry.
   */
  public function setLease($lease_id, $data, $expires) {
    $this->storage->setWithExpire($lease_id, $data, $expires);
  }

}