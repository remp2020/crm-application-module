<?php

namespace Crm\ApplicationModule\User;

interface UserDataProviderInterface
{
    public static function identifier(): string;

    /**
     * data provides user-related data for application use. It might be provided to the third party applications,
     * frontend or cached for later use.
     * Only frequently accessed data should be returned here.
     *
     * @param $userId
     * @return mixed
     */
    public function data($userId);

    /**
     * download provides user-related data that should be included within zip provided to user when his/her data is
     * requested. Download data are in general more extensive than what is returned in `data()` and might contain
     * system information (e.g. IP address, dates of updates, etc.)
     *
     * @param $userId
     * @return mixed
     */
    public function download($userId);

    /**
     * downloadAttachments provides with attachments that should be included within zip file provided to user when his/her
     * data is requested. Only non-text data that cannot be returned within download() method should be included here -
     * such as invoices.
     *
     * The return value should include key-value pairs, where:
     *   * key: file name that should be used for the file
     *   * path: full resolvable path for reading the file
     *
     * @param $userId
     * @return mixed
     */
    public function downloadAttachments($userId);


    /**
     * delete deletes or anonymize user data in a non-reversible fashion.
     *
     * @param $userId
     * @param array $protectedData
     * @return mixed
     */
    public function delete($userId, $protectedData = []);

    /**
     * protect serves as a way to communicate between the data providers. One data provider can flag instances of records
     * belonging to other data provider so they're not removed when deleting the data. For example OrdersModule can flag
     * shipping addresses that are required for possible future claims so that AddressUserDataProvider won't delete them
     * when deleting the rest of addresses.
     *
     * The return value should be in key-value format where:
     *   * key: identifier of data provider your implementation targets (e.g. AddressUserDataProvider::identifier() if
     *          you want to protect specific addresses)
     *   * value: array of ids identifying protected instances
     *
     * @param $userId
     * @return array
     */
    public function protect($userId): array;

    /**
     * canBeDeleted returns array with two values:
     *   * boolean whether user can be deleted or not and list of string error
     *   * messages explaining why user cannot be deleted
     *
     * @param $userId
     * @return array
     */
    public function canBeDeleted($userId): array;
}
