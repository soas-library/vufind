<?php
/**
 * MyResearch Controller
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2010.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category VuFind
 * @package  Controller
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Site
 */
namespace VuFind\Controller;

use VuFind\Exception\Auth as AuthException,
    VuFind\Exception\Forbidden as ForbiddenException,
    VuFind\Exception\Mail as MailException,
    VuFind\Exception\ListPermission as ListPermissionException,
    VuFind\Exception\RecordMissing as RecordMissingException,
    /** SCB **/
    Zend\Http\Client,
    Zend\Http\Request,
    /** SCB **/
    VuFind\Search\RecommendListener, Zend\Stdlib\Parameters,
    Zend\View\Model\ViewModel;
    
    /** SCB **/
    
    // Temp!
// Putting this here as I do not have write access to the modules directory where it should really 
// live.

/**
 * Class WpmXmlBuilder is responsible for creating an XML structure that fits the WPM specification, so that library
 * fines can be paid. It is designed to be called from the finesAction() method in VuFind.
 *
 */

class WpmXmlBuilder {

    /**
     * @var string
     */
    protected $transactionReference;

    /**
     * @var string the student Id
     */
    protected $clientId;

    /**
     * @var string
     */
    protected $sharedSecret;

    /**
     * @var array of numbers
     */
    protected $payments;

    /**
     * @var array
     */
    protected $expectedParams = array(
        'barcode',
	'clientId',
        'transactionReference',
        'sharedSecret',
        'payments',
        'user',
        'redirectUrl',
        'cancelUrl',
        'callbackUrl',
        'pathwayId',
        'departmentId',
        'emailFrom',
    );

    /**
     * @var string
     */
    protected $redirectUrl;

    /**
     * @var string
     */
    protected $cancelUrl;

    /**
     * @param array $attributes
     */
    public function __construct($attributes) {

        foreach ($this->expectedParams as $paramName) {
            if (empty($attributes[$paramName])) {
                throw new \InvalidArgumentException("{$paramName} missing from params");
            }
            $this->$paramName = $attributes[$paramName];
        }
    }

    /**
     * @return string XML
     */
    public function getXml() {

        $xmlRoot = '<?xml version="1.0" encoding="utf-8"?><wpmpaymentrequest></wpmpaymentrequest>';
        $xml_builder = new \SimpleXMLElement($xmlRoot);

        // Md5 hash digest for authentication
        $concatenatedBits = $this->clientId . $this->transactionReference . $this->totalAmountToPay() . $this->sharedSecret;
        $md5Hash = md5($concatenatedBits);
        $xml_builder['msgid'] = $md5Hash;

        $xml_builder->clientid = $this->clientId;
        $xml_builder->requesttype = 1;
        $xml_builder->pathwayid = $this->pathwayId;
        $xml_builder->departmentid = $this->departmentId;
        $xml_builder->staffid = '';

        $this->addCdataChild($xml_builder, 'customerid', $this->user->username);
        $xml_builder->title = '';
        $xml_builder->firstname = '';
        $xml_builder->middlename = '';
        $xml_builder->lastname = '';
	$xml_builder->barcode = $this->barcode;
        $this->addCdataChild($xml_builder, 'toemail', (empty($email)) ? 'test@example.com' : $email);
        $this->addCdataChild($xml_builder, 'transactionreference', $this->transactionReference);

        $this->addCdataChild($xml_builder, 'redirecturl', $this->redirectUrl);
        $this->addCdataChild($xml_builder, 'callbackurl', $this->callbackUrl);
        $this->addCdataChild($xml_builder, 'cancelurl', $this->cancelUrl);

        $this->addCdataChild($xml_builder, 'emailfrom', $this->emailFrom);

        $xml_builder->addChild('payments');
        $xml_builder->payments->addAttribute('type', 'PN');
        $xml_builder->payments->addAttribute('id', '1');
        $xml_builder->payments->addAttribute('payoption', 'LF');

        $this->addCdataChild($xml_builder->payments, 'description', 'Your library fines');

        // Only one payment as per the XML spec.
        /**
         * @var SimpleXmlElement $xmlPayment
         */
        $xmlPayment = $xml_builder->payments->addChild('payment');
        $xmlPayment->addAttribute('payid', 1);

        $xmlPayment->addChild('amounttopay', sprintf('%0.2f', array_sum($this->payments)));
        $xmlPayment->addChild('amounttopayvat', '0.00');
        $xmlPayment->addChild('amounttopayexvat', sprintf('%0.2f', array_sum($this->payments)));
        $this->addCdataChild($xmlPayment, 'vatdesc', 'Zero rate VAT');

        $this->addCdataChild($xmlPayment, 'vatcode', 'Z');
        $xmlPayment->addChild('vatrate', '0');
        $xmlPayment->addChild('dateofpayment', strftime('%F %T'));

        $xmlPayment->addChild('editable', '0');
        $xmlPayment->addChild('mandatory', '1');
        return $xml_builder->asXML();

    }

    /**
     * @return int
     */
    private function totalAmountToPay() {
        return sprintf('%0.2f', array_sum($this->payments));
    }

    /**
     * @param SimpleXMLElement $xmlObject
     * @param string $name
     * @param string $value
     */
    private function addCdataChild($xmlObject, $name, $value){
        $new_child = $xmlObject->addChild($name);

        if ($new_child !== NULL) {
            $node = dom_import_simplexml($new_child);
            $no = $node->ownerDocument;
            $node->appendChild($no->createCDATASection($value));
        }
    }
}

/**
 * This class takes in the XML response from the payment gateway and tests to see if the message id is valid.
 * This prevents fraud by way of people directly submitting data to the callback url.
 */
class WpmXmlValidator {

    /**
     * @param $xml
     * @param $sharedSecret
     */
    public function __construct($xml, $sharedSecret) {
        $this->xml = simplexml_load_string($xml);
        $this->sharedSecret = $sharedSecret;
    }

    /**
     * @return bool
     */
    public function valid() {
        $expected = $this->expectedMessageId();
        $actual = $this->sentMessageId();
        return $expected == $actual;
    }

    /**
     * Returns the message id that we got back from the payment gateway (or wherever the POST came from).
     *
     * @return string
     */
    public function sentMessageId() {
        $attrs = $this->xml->attributes();
        return (string)$attrs['msgid'];
    }

    /**
     * Takes the details of the POST data and tells us what the message id should have been.
     *
     * @return string
     */
    public function expectedMessageId() {
        $clientId = trim($this->xml->clientid);
        $transactionReference = trim($this->xml->transactionreference);
        $payments = $this->xml->payments->xpath('//amounttopay');
        $totalAmountToPay = sprintf('%0.2f', $payments[0]);
        $sharedSecret = $this->sharedSecret;
        $concatenatedBits = $clientId . $transactionReference . $totalAmountToPay . $sharedSecret;
        return md5($concatenatedBits);
    }

    /**
     * @return bool
     */
    public function isPaid() {
        $payments = $this->xml->payments->xpath('//payment');
        $payid = $payments[0]['paid'];
        return $payid == 1;
    }

   /**
     * @return string
     */
    public function transId() {
        $transId = $this->xml->transaction->xpath('//transid');
        return $transId[0];
    }

    /**
     * Returns an array of billIds that are no paid
     *
     * @return array
     */
    public function billIds() {
        return explode(' ', trim($this->xml->transactionreference));
    }

    /**
     * @return string
     */
    public function paymentDate() {
        $payments = $this->xml->payments->xpath('//payment');
        return $payments[0]->dateofpayment;    
    }

    /**
    * return amount paid
    */
    public function amtPaid() {
	$totalPaid = $this->xml->transaction->xpath('//totalpaid');
        return $totalPaid[0];
    }

    /**
    *return barcode
    **/
    public function barcode() {
	$barcode = $this->xml->barcode;
	return $barcode[0];
    } 
    
    /**
    *return failure_reason
    **/
    public function failureReason() {
	$failureReason = $this->xml->transaction->xpath('//failurereason');
        return $failureReason[0];
    } 
}
    
    /** SCB **/

/**
 * Controller for the user account area.
 *
 * @category VuFind
 * @package  Controller
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Site
 */
class MyResearchController extends AbstractBase
{
     
     /** SCB **/
     
     /**
     * @var string
     */
    protected $paymentEmailFrom = 'library.systems@soas.ac.uk';
    
    /**
     * @var string
     */
    protected $paymentPostUrl = 'https://payments.soas.ac.uk/kuali';
     
     /** SCB **/
     
    /**
     * Process an authentication error.
     *
     * @param AuthException $e Exception to process.
     *
     * @return void
     */
    protected function processAuthenticationException(AuthException $e)
    {
        
        /** SCB **/
        
        // If we are arriving via the payment system callback, we want to avoid being bounced
        // to the login page.
        //print_r($e);
        //die();
        
        /** SCB **/
        
        $msg = $e->getMessage();
        // If a Shibboleth-style login has failed and the user just logged
        // out, we need to override the error message with a more relevant
        // one:
        if ($msg == 'authentication_error_admin'
            && $this->getAuthManager()->userHasLoggedOut()
            && $this->getSessionInitiator()
        ) {
            $msg = 'authentication_error_loggedout';
        }
        $this->flashMessenger()->addMessage($msg, 'error');
    }

    /**
     * Maintaining this method for backwards compatibility;
     * logic moved to parent and method re-named
     *
     * @return void
     */
    protected function storeRefererForPostLoginRedirect()
    {
        $this->setFollowupUrlToReferer();
    }

    /**
     * Prepare and direct the home page where it needs to go
     *
     * @return mixed
     */
    public function homeAction()
    {
        // Process login request, if necessary (either because a form has been
        // submitted or because we're using an external login provider):
        if ($this->params()->fromPost('processLogin')
            || $this->getSessionInitiator()
            || $this->params()->fromPost('auth_method')
            || $this->params()->fromQuery('auth_method')
        ) {
            try {
                if (!$this->getAuthManager()->isLoggedIn()) {
                   $this->getAuthManager()->login($this->getRequest());
                }
            } catch (AuthException $e) {
                $this->processAuthenticationException($e);
            }
        }

        // Not logged in?  Force user to log in:
        if (!$this->getAuthManager()->isLoggedIn()) {

            $this->setFollowupUrlToReferer();
            return $this->forwardTo('MyResearch', 'Login');
        }
        // Logged in?  Forward user to followup action
        // or default action (if no followup provided):

        if ($url = $this->getFollowupUrl()) {
            $this->clearFollowupUrl();
            // If a user clicks on the "Your Account" link, we want to be sure
            // they get to their account rather than being redirected to an old
            // followup URL. We'll use a redirect=0 GET flag to indicate this:
            if ($this->params()->fromQuery('redirect', true)) {
                return $this->redirect()->toUrl($url);
            }
        }

        $config = $this->getConfig();
        $page = isset($config->Site->defaultAccountPage)
            ? $config->Site->defaultAccountPage : 'Favorites';

        // Default to search history if favorites are disabled:
        if ($page == 'Favorites' && !$this->listsEnabled()) {
            return $this->forwardTo('Search', 'History');
        }

        return $this->forwardTo('MyResearch', $page);
    }

    /**
     * "Create account" action
     *
     * @return mixed
     */
    public function accountAction()
    {
        // If the user is already logged in, don't let them create an account:
        if ($this->getAuthManager()->isLoggedIn()) {
            return $this->redirect()->toRoute('myresearch-home');
        }
        // If authentication mechanism does not support account creation, send
        // the user away!
        $method = trim($this->params()->fromQuery('auth_method'));
        if (!$this->getAuthManager()->supportsCreation($method)) {
            return $this->forwardTo('MyResearch', 'Home');
        }

        // If there's already a followup url, keep it; otherwise set one.
        if (!$this->getFollowupUrl()) {
            $this->setFollowupUrlToReferer();
        }

        // Make view
        $view = $this->createViewModel();
        // Password policy
        $view->passwordPolicy = $this->getAuthManager()
            ->getPasswordPolicy($method);
        // Set up reCaptcha
        $view->useRecaptcha = $this->recaptcha()->active('newAccount');
        // Pass request to view so we can repopulate user parameters in form:
        $view->request = $this->getRequest()->getPost();
        // Process request, if necessary:
        if ($this->formWasSubmitted('submit', $view->useRecaptcha)) {
            try {
                $this->getAuthManager()->create($this->getRequest());
                return $this->forwardTo('MyResearch', 'Home');
            } catch (AuthException $e) {
                $this->flashMessenger()->addMessage($e->getMessage(), 'error');
            }
        } else {
            // If we are not processing a submission, we need to simply display
            // an empty form. In case ChoiceAuth is being used, we may need to
            // override the active authentication method based on request
            // parameters to ensure display of the appropriate template.
            $this->setUpAuthenticationFromRequest();
        }
        return $view;
    }

    /**
     * Login Action
     *
     * @return mixed
     */
    public function loginAction()
    {
        // If this authentication method doesn't use a VuFind-generated login
        // form, force it through:
        if ($this->getSessionInitiator()) {
            // Don't get stuck in an infinite loop -- if processLogin is already
            // set, it probably means Home action is forwarding back here to
            // report an error!
            //
            // Also don't attempt to process a login that hasn't happened yet;
            // if we've just been forced here from another page, we need the user
            // to click the session initiator link before anything can happen.
            if (!$this->params()->fromPost('processLogin', false)
                && !$this->params()->fromPost('forcingLogin', false)
            ) {
                $this->getRequest()->getPost()->set('processLogin', true);
                return $this->forwardTo('MyResearch', 'Home');
            }
        }

        // Make request available to view for form updating:
        $view = $this->createViewModel();
        $view->request = $this->getRequest()->getPost();
        return $view;
    }

    /**
     * User login action -- clear any previous follow-up information prior to
     * triggering a login process. This is used for explicit login links within
     * the UI to differentiate them from contextual login links that are triggered
     * by attempting to access protected actions.
     *
     * @return mixed
     */
    public function userloginAction()
    {
        // Don't log in if already logged in!
        if ($this->getAuthManager()->isLoggedIn()) {
            // inLightbox (only instance)
            if ($this->getRequest()->getQuery('layout', 'no') === 'lightbox'
                || 'layout/lightbox' == $this->layout()->getTemplate()
            ) {
                $response = $this->getResponse();
                $response->setStatusCode(205);
                return $response;
            }
            return $this->redirect()->toRoute('home');
        }
        $this->clearFollowupUrl();
        $this->setFollowupUrlToReferer();
        if ($si = $this->getSessionInitiator()) {
            return $this->redirect()->toUrl($si);
        }
        return $this->forwardTo('MyResearch', 'Login');
    }

    /**
     * Logout Action
     *
     * @return mixed
     */
    public function logoutAction()
    {
        $config = $this->getConfig();
        if (isset($config->Site->logOutRoute)) {
            $logoutTarget = $this->getServerUrl($config->Site->logOutRoute);
        } else {
            $logoutTarget = $this->getRequest()->getServer()->get('HTTP_REFERER');
            if (empty($logoutTarget)) {
                $logoutTarget = $this->getServerUrl('home');
            }

            // If there is an auth_method parameter in the query, we should strip
            // it out. Otherwise, the user may get stuck in an infinite loop of
            // logging out and getting logged back in when using environment-based
            // authentication methods like Shibboleth.
            $logoutTarget = preg_replace(
                '/([?&])auth_method=[^&]*&?/', '$1', $logoutTarget
            );
            $logoutTarget = rtrim($logoutTarget, '?');

            // Another special case: if logging out will send the user back to
            // the MyResearch home action, instead send them all the way to
            // VuFind home. Otherwise, they might get logged back in again,
            // which is confusing. Even in the best scenario, they'll just end
            // up on a login screen, which is not helpful.
            if ($logoutTarget == $this->getServerUrl('myresearch-home')) {
                $logoutTarget = $this->getServerUrl('home');
            }
        }

        return $this->redirect()
            ->toUrl($this->getAuthManager()->logout($logoutTarget));
    }

    /**
     * Support method for savesearchAction(): set the saved flag in a secure
     * fashion, throwing an exception if somebody attempts something invalid.
     *
     * @param int  $searchId The search ID to save/unsave
     * @param bool $saved    The new desired state of the saved flag
     * @param int  $userId   The user ID requesting the change
     *
     * @throws \Exception
     * @return void
     */
    protected function setSavedFlagSecurely($searchId, $saved, $userId)
    {
        $searchTable = $this->getTable('Search');
        $sessId = $this->getServiceLocator()->get('VuFind\SessionManager')->getId();
        $row = $searchTable->getOwnedRowById($searchId, $sessId, $userId);
        if (empty($row)) {
            throw new ForbiddenException('Access denied.');
        }
        $row->saved = $saved ? 1 : 0;
        $row->user_id = $userId;
        $row->save();
    }

    /**
     * Handle 'save/unsave search' request
     *
     * @return mixed
     */
    public function savesearchAction()
    {
        // Fail if saved searches are disabled.
        $check = $this->getServiceLocator()->get('VuFind\AccountCapabilities');
        if ($check->getSavedSearchSetting() === 'disabled') {
            throw new ForbiddenException('Saved searches disabled.');
        }

        $user = $this->getUser();
        if ($user == false) {
            return $this->forceLogin();
        }

        // Check for the save / delete parameters and process them appropriately:
        if (($id = $this->params()->fromQuery('save', false)) !== false) {
            $this->setSavedFlagSecurely($id, true, $user->id);
            $this->flashMessenger()->addMessage('search_save_success', 'success');
        } else if (($id = $this->params()->fromQuery('delete', false)) !== false) {
            $this->setSavedFlagSecurely($id, false, $user->id);
            $this->flashMessenger()->addMessage('search_unsave_success', 'success');
        } else {
            throw new \Exception('Missing save and delete parameters.');
        }

        // Forward to the appropriate place:
        if ($this->params()->fromQuery('mode') == 'history') {
            return $this->redirect()->toRoute('search-history');
        } else {
            // Forward to the Search/Results action with the "saved" parameter set;
            // this will in turn redirect the user to the appropriate results screen.
            $this->getRequest()->getQuery()->set('saved', $id);
            return $this->forwardTo('Search', 'Results');
        }
    }

    /**
     * Gather user profile data
     *
     * @return mixed
     */
    public function profileAction()
    {
        // Stop now if the user does not have valid catalog credentials available:
        if (!is_array($patron = $this->catalogLogin())) {
            return $patron;
        }

        // User must be logged in at this point, so we can assume this is non-false:
        $user = $this->getUser();

        // Process home library parameter (if present):
        $homeLibrary = $this->params()->fromPost('home_library', false);
        if (!empty($homeLibrary)) {
            $user->changeHomeLibrary($homeLibrary);
            $this->getAuthManager()->updateSession($user);
            $this->flashMessenger()->addMessage('profile_update', 'success');
        }

        // Begin building view object:
        $view = $this->createViewModel();

        // Obtain user information from ILS:
        $catalog = $this->getILS();
        $profile = $catalog->getMyProfile($patron);
        $profile['home_library'] = $user->home_library;
        $view->profile = $profile;
        try {
            $view->pickup = $catalog->getPickUpLocations($patron);
            $view->defaultPickupLocation
                = $catalog->getDefaultPickUpLocation($patron);
        } catch (\Exception $e) {
            // Do nothing; if we're unable to load information about pickup
            // locations, they are not supported and we should ignore them.
        }

        return $view;
    }

    /**
     * Catalog Login Action
     *
     * @return mixed
     */
    public function catalogloginAction()
    {
        // Connect to the ILS and check if multiple target support is available:
        $targets = null;
        $catalog = $this->getILS();
        if ($catalog->checkCapability('getLoginDrivers')) {
            $targets = $catalog->getLoginDrivers();
        }
        return $this->createViewModel(['targets' => $targets]);
    }

    /**
     * Action for sending all of a user's saved favorites to the view
     *
     * @return mixed
     */
    public function favoritesAction()
    {
        // Favorites is the same as MyList, but without the list ID parameter.
        return $this->forwardTo('MyResearch', 'MyList');
    }

    /**
     * Delete group of records from favorites.
     *
     * @return mixed
     */
    public function deleteAction()
    {
        // Force login:
        $user = $this->getUser();
        if (!$user) {
            return $this->forceLogin();
        }

        // Get target URL for after deletion:
        $listID = $this->params()->fromPost('listID');
        $newUrl = empty($listID)
            ? $this->url()->fromRoute('myresearch-favorites')
            : $this->url()->fromRoute('userList', ['id' => $listID]);

        // Fail if we have nothing to delete:
        $ids = is_null($this->params()->fromPost('selectAll'))
            ? $this->params()->fromPost('ids')
            : $this->params()->fromPost('idsAll');
        if (!is_array($ids) || empty($ids)) {
            $this->flashMessenger()->addMessage('bulk_noitems_advice', 'error');
            return $this->redirect()->toUrl($newUrl);
        }

        // Process the deletes if necessary:
        if ($this->formWasSubmitted('submit')) {
            $this->favorites()->delete($ids, $listID, $user);
            $this->flashMessenger()->addMessage('fav_delete_success', 'success');
            return $this->redirect()->toUrl($newUrl);
        }

        // If we got this far, the operation has not been confirmed yet; show
        // the necessary dialog box:
        if (empty($listID)) {
            $list = false;
        } else {
            $table = $this->getTable('UserList');
            $list = $table->getExisting($listID);
        }
        return $this->createViewModel(
            [
                'list' => $list, 'deleteIDS' => $ids,
                'records' => $this->getRecordLoader()->loadBatch($ids)
            ]
        );
    }

    /**
     * Delete record
     *
     * @param string $id     ID of record to delete
     * @param string $source Source of record to delete
     *
     * @return mixed         True on success; otherwise returns a value that can
     * be returned by the controller to forward to another action (i.e. force login)
     */
    public function performDeleteFavorite($id, $source)
    {
        // Force login:
        $user = $this->getUser();
        if (!$user) {
            return $this->forceLogin();
        }

        // Load/check incoming parameters:
        $listID = $this->params()->fromRoute('id');
        $listID = empty($listID) ? null : $listID;
        if (empty($id)) {
            throw new \Exception('Cannot delete empty ID!');
        }

        // Perform delete and send appropriate flash message:
        if (null !== $listID) {
            // ...Specific List
            $table = $this->getTable('UserList');
            $list = $table->getExisting($listID);
            $list->removeResourcesById($user, [$id], $source);
            $this->flashMessenger()->addMessage('Item removed from list', 'success');
        } else {
            // ...My Favorites
            $user->removeResourcesById([$id], $source);
            $this->flashMessenger()
                ->addMessage('Item removed from favorites', 'success');
        }

        // All done -- return true to indicate success.
        return true;
    }

    /**
     * Process the submission of the edit favorite form.
     *
     * @param \VuFind\Db\Row\User               $user   Logged-in user
     * @param \VuFind\RecordDriver\AbstractBase $driver Record driver for favorite
     * @param int                               $listID List being edited (null
     * if editing all favorites)
     *
     * @return object
     */
    protected function processEditSubmit($user, $driver, $listID)
    {
        $lists = $this->params()->fromPost('lists');
        $tagParser = $this->getServiceLocator()->get('VuFind\Tags');
        foreach ($lists as $list) {
            $tags = $this->params()->fromPost('tags' . $list);
            $driver->saveToFavorites(
                [
                    'list'  => $list,
                    'mytags'  => $tagParser->parse($tags),
                    'notes' => $this->params()->fromPost('notes' . $list)
                ],
                $user
            );
        }
        // add to a new list?
        $addToList = $this->params()->fromPost('addToList');
        if ($addToList > -1) {
            $driver->saveToFavorites(['list' => $addToList], $user);
        }
        $this->flashMessenger()->addMessage('edit_list_success', 'success');

        $newUrl = is_null($listID)
            ? $this->url()->fromRoute('myresearch-favorites')
            : $this->url()->fromRoute('userList', ['id' => $listID]);
        return $this->redirect()->toUrl($newUrl);
    }

    /**
     * Edit record
     *
     * @return mixed
     */
    public function editAction()
    {
        // Force login:
        $user = $this->getUser();
        if (!$user) {
            return $this->forceLogin();
        }

        // Get current record (and, if applicable, selected list ID) for convenience:
        $id = $this->params()->fromPost('id', $this->params()->fromQuery('id'));
        $source = $this->params()->fromPost(
            'source', $this->params()->fromQuery('source', DEFAULT_SEARCH_BACKEND)
        );
        $driver = $this->getRecordLoader()->load($id, $source, true);
        $listID = $this->params()->fromPost(
            'list_id', $this->params()->fromQuery('list_id', null)
        );

        // Process save action if necessary:
        if ($this->formWasSubmitted('submit')) {
            return $this->processEditSubmit($user, $driver, $listID);
        }

        // Get saved favorites for selected list (or all lists if $listID is null)
        $userResources = $user->getSavedData($id, $listID, $source);
        $savedData = [];
        foreach ($userResources as $current) {
            $savedData[] = [
                'listId' => $current->list_id,
                'listTitle' => $current->list_title,
                'notes' => $current->notes,
                'tags' => $user->getTagString($id, $current->list_id, $source)
            ];
        }

        // In order to determine which lists contain the requested item, we may
        // need to do an extra database lookup if the previous lookup was limited
        // to a particular list ID:
        $containingLists = [];
        if (!empty($listID)) {
            $userResources = $user->getSavedData($id, null, $source);
        }
        foreach ($userResources as $current) {
            $containingLists[] = $current->list_id;
        }

        // Send non-containing lists to the view for user selection:
        $userLists = $user->getLists();
        $lists = [];
        foreach ($userLists as $userList) {
            if (!in_array($userList->id, $containingLists)) {
                $lists[$userList->id] = $userList->title;
            }
        }

        return $this->createViewModel(
            [
                'driver' => $driver, 'lists' => $lists, 'savedData' => $savedData
            ]
        );
    }

    /**
     * Confirm a request to delete a favorite item.
     *
     * @param string $id     ID of record to delete
     * @param string $source Source of record to delete
     *
     * @return mixed
     */
    protected function confirmDeleteFavorite($id, $source)
    {
        // Normally list ID is found in the route match, but in lightbox context it
        // may sometimes be a GET parameter.  We must cover both cases.
        $listID = $this->params()->fromRoute('id', $this->params()->fromQuery('id'));
        if (empty($listID)) {
            $url = $this->url()->fromRoute('myresearch-favorites');
        } else {
            $url = $this->url()->fromRoute('userList', ['id' => $listID]);
        }
        return $this->confirm(
            'confirm_delete_brief', $url, $url, 'confirm_delete',
            ['delete' => $id, 'source' => $source]
        );
    }

    /**
     * Send user's saved favorites from a particular list to the view
     *
     * @return mixed
     */
    public function mylistAction()
    {
        // Fail if lists are disabled:
        if (!$this->listsEnabled()) {
            throw new ForbiddenException('Lists disabled');
        }

        // Check for "delete item" request; parameter may be in GET or POST depending
        // on calling context.
        $deleteId = $this->params()->fromPost(
            'delete', $this->params()->fromQuery('delete')
        );
        if ($deleteId) {
            $deleteSource = $this->params()->fromPost(
                'source',
                $this->params()->fromQuery('source', DEFAULT_SEARCH_BACKEND)
            );
            // If the user already confirmed the operation, perform the delete now;
            // otherwise prompt for confirmation:
            $confirm = $this->params()->fromPost(
                'confirm', $this->params()->fromQuery('confirm')
            );
            if ($confirm) {
                $success = $this->performDeleteFavorite($deleteId, $deleteSource);
                if ($success !== true) {
                    return $success;
                }
            } else {
                return $this->confirmDeleteFavorite($deleteId, $deleteSource);
            }
        }

        // If we got this far, we just need to display the favorites:
        try {
            $runner = $this->getServiceLocator()->get('VuFind\SearchRunner');

            // We want to merge together GET, POST and route parameters to
            // initialize our search object:
            $request = $this->getRequest()->getQuery()->toArray()
                + $this->getRequest()->getPost()->toArray()
                + ['id' => $this->params()->fromRoute('id')];

            // Set up listener for recommendations:
            $rManager = $this->getServiceLocator()
                ->get('VuFind\RecommendPluginManager');
            $setupCallback = function ($runner, $params, $searchId) use ($rManager) {
                $listener = new RecommendListener($rManager, $searchId);
                $listener->setConfig(
                    $params->getOptions()->getRecommendationSettings()
                );
                $listener->attach($runner->getEventManager()->getSharedManager());
            };

            $results = $runner->run($request, 'Favorites', $setupCallback);
            return $this->createViewModel(
                ['params' => $results->getParams(), 'results' => $results]
            );
        } catch (ListPermissionException $e) {
            if (!$this->getUser()) {
                return $this->forceLogin();
            }
            throw $e;
        }
    }

    /**
     * Process the "edit list" submission.
     *
     * @param \VuFind\Db\Row\User     $user Logged in user
     * @param \VuFind\Db\Row\UserList $list List being created/edited
     *
     * @return object|bool                  Response object if redirect is
     * needed, false if form needs to be redisplayed.
     */
    protected function processEditList($user, $list)
    {
        // Process form within a try..catch so we can handle errors appropriately:
        try {
            $finalId
                = $list->updateFromRequest($user, $this->getRequest()->getPost());

            // If the user is in the process of saving a record, send them back
            // to the save screen; otherwise, send them back to the list they
            // just edited.
            $recordId = $this->params()->fromQuery('recordId');
            $recordSource
                = $this->params()->fromQuery('recordSource', DEFAULT_SEARCH_BACKEND);
            if (!empty($recordId)) {
                $details = $this->getRecordRouter()->getActionRouteDetails(
                    $recordSource . '|' . $recordId, 'Save'
                );
                return $this->redirect()->toRoute(
                    $details['route'], $details['params']
                );
            }

            // Similarly, if the user is in the process of bulk-saving records,
            // send them back to the appropriate place in the cart.
            $bulkIds = $this->params()->fromPost(
                'ids', $this->params()->fromQuery('ids', [])
            );
            if (!empty($bulkIds)) {
                $params = [];
                foreach ($bulkIds as $id) {
                    $params[] = urlencode('ids[]') . '=' . urlencode($id);
                }
                $saveUrl = $this->url()->fromRoute('cart-save');
                $saveUrl .= (strpos($saveUrl, '?') === false) ? '?' : '&';
                return $this->redirect()
                    ->toUrl($saveUrl . implode('&', $params));
            }

            return $this->redirect()->toRoute('userList', ['id' => $finalId]);
        } catch (\Exception $e) {
            switch(get_class($e)) {
            case 'VuFind\Exception\ListPermission':
            case 'VuFind\Exception\MissingField':
                $this->flashMessenger()->addMessage($e->getMessage(), 'error');
                return false;
            case 'VuFind\Exception\LoginRequired':
                return $this->forceLogin();
            default:
                throw $e;
            }
        }
    }

    /**
     * Send user's saved favorites from a particular list to the edit view
     *
     * @return mixed
     */
    public function editlistAction()
    {
        // Fail if lists are disabled:
        if (!$this->listsEnabled()) {
            throw new ForbiddenException('Lists disabled');
        }

        // User must be logged in to edit list:
        $user = $this->getUser();
        if ($user == false) {
            return $this->forceLogin();
        }

        // Is this a new list or an existing list?  Handle the special 'NEW' value
        // of the ID parameter:
        $id = $this->params()->fromRoute('id', $this->params()->fromQuery('id'));
        $table = $this->getTable('UserList');
        $newList = ($id == 'NEW');
        $list = $newList ? $table->getNew($user) : $table->getExisting($id);

        // Make sure the user isn't fishing for other people's lists:
        if (!$newList && !$list->editAllowed($user)) {
            throw new ListPermissionException('Access denied.');
        }

        // Process form submission:
        if ($this->formWasSubmitted('submit')) {
            if ($redirect = $this->processEditList($user, $list)) {
                return $redirect;
            }
        }

        // Send the list to the view:
        return $this->createViewModel(['list' => $list, 'newList' => $newList]);
    }

    /**
     * Creates a confirmation box to delete or not delete the current list
     *
     * @return mixed
     */
    public function deletelistAction()
    {
        // Fail if lists are disabled:
        if (!$this->listsEnabled()) {
            throw new ForbiddenException('Lists disabled');
        }

        // Get requested list ID:
        $listID = $this->params()
            ->fromPost('listID', $this->params()->fromQuery('listID'));

        // Have we confirmed this?
        $confirm = $this->params()->fromPost(
            'confirm', $this->params()->fromQuery('confirm')
        );
        if ($confirm) {
            try {
                $table = $this->getTable('UserList');
                $list = $table->getExisting($listID);
                $list->delete($this->getUser());

                // Success Message
                $this->flashMessenger()->addMessage('fav_list_delete', 'success');
            } catch (\Exception $e) {
                switch(get_class($e)) {
                case 'VuFind\Exception\LoginRequired':
                case 'VuFind\Exception\ListPermission':
                    $user = $this->getUser();
                    if ($user == false) {
                        return $this->forceLogin();
                    }
                    // Logged in? Fall through to default case!
                default:
                    throw $e;
                }
            }
            // Redirect to MyResearch home
            return $this->redirect()->toRoute('myresearch-favorites');
        }

        // If we got this far, we must display a confirmation message:
        return $this->confirm(
            'confirm_delete_list_brief',
            $this->url()->fromRoute('myresearch-deletelist'),
            $this->url()->fromRoute('userList', ['id' => $listID]),
            'confirm_delete_list_text', ['listID' => $listID]
        );
    }

    /**
     * Get a record driver object corresponding to an array returned by an ILS
     * driver's getMyHolds / getMyTransactions method.
     *
     * @param array $current Record information
     *
     * @return \VuFind\RecordDriver\AbstractBase
     */
    protected function getDriverForILSRecord($current)
    {
        $id = isset($current['id']) ? $current['id'] : null;
        $source = isset($current['source'])
            ? $current['source'] : DEFAULT_SEARCH_BACKEND;
        $record = $this->getServiceLocator()->get('VuFind\RecordLoader')
            ->load($id, $source, true);
        $record->setExtraDetail('ils_details', $current);
        return $record;
    }

    /**
     * Send list of holds to view
     *
     * @return mixed
     */
    public function holdsAction()
    {
        // Stop now if the user does not have valid catalog credentials available:
        if (!is_array($patron = $this->catalogLogin())) {
            return $patron;
        }

        // Connect to the ILS:
        $catalog = $this->getILS();

        // Process cancel requests if necessary:
        $cancelStatus = $catalog->checkFunction('cancelHolds', compact('patron'));
        $view = $this->createViewModel();
        $view->cancelResults = $cancelStatus
            ? $this->holds()->cancelHolds($catalog, $patron) : [];
        // If we need to confirm
        if (!is_array($view->cancelResults)) {
            return $view->cancelResults;
        }

        // By default, assume we will not need to display a cancel form:
        
        /** SCB **/
        //$view->cancelForm = false;
        $view->cancelForm = true;
        /** SCB **/

        // Get held item details:
        $result = $catalog->getMyHolds($patron);
        $recordList = [];
        $this->holds()->resetValidation();
        foreach ($result as $current) {
            // Add cancel details if appropriate:
            $current = $this->holds()->addCancelDetails(
                $catalog, $current, $cancelStatus
            );
            if ($cancelStatus && $cancelStatus['function'] != "getCancelHoldLink"
                && isset($current['cancel_details'])
            ) {
                // Enable cancel form if necessary:
                $view->cancelForm = true;
            }

            // Build record driver:
            $recordList[] = $this->getDriverForILSRecord($current);
        }

        // Get List of PickUp Libraries based on patron's home library
        try {
            $view->pickup = $catalog->getPickUpLocations($patron);
        } catch (\Exception $e) {
            // Do nothing; if we're unable to load information about pickup
            // locations, they are not supported and we should ignore them.
        }
        $view->recordList = $recordList;
        return $view;
    }

    /**
     * Send list of storage retrieval requests to view
     *
     * @return mixed
     */
    public function storageRetrievalRequestsAction()
    {
        // Stop now if the user does not have valid catalog credentials available:
        if (!is_array($patron = $this->catalogLogin())) {
            return $patron;
        }

        // Connect to the ILS:
        $catalog = $this->getILS();

        // Process cancel requests if necessary:
        $cancelSRR = $catalog->checkFunction(
            'cancelStorageRetrievalRequests', compact('patron')
        );
        $view = $this->createViewModel();
        $view->cancelResults = $cancelSRR
            ? $this->storageRetrievalRequests()->cancelStorageRetrievalRequests(
                $catalog, $patron
            )
            : [];
        // If we need to confirm
        if (!is_array($view->cancelResults)) {
            return $view->cancelResults;
        }

        // By default, assume we will not need to display a cancel form:
        $view->cancelForm = false;

        // Get request details:
        $result = $catalog->getMyStorageRetrievalRequests($patron);
        $recordList = [];
        $this->storageRetrievalRequests()->resetValidation();
        foreach ($result as $current) {
            // Add cancel details if appropriate:
            $current = $this->storageRetrievalRequests()->addCancelDetails(
                $catalog, $current, $cancelSRR, $patron
            );
            if ($cancelSRR
                && $cancelSRR['function'] != "getCancelStorageRetrievalRequestLink"
                && isset($current['cancel_details'])
            ) {
                // Enable cancel form if necessary:
                $view->cancelForm = true;
            }

            // Build record driver:
            $recordList[] = $this->getDriverForILSRecord($current);
        }

        // Get List of PickUp Libraries based on patron's home library
        try {
            $view->pickup = $catalog->getPickUpLocations($patron);
        } catch (\Exception $e) {
            // Do nothing; if we're unable to load information about pickup
            // locations, they are not supported and we should ignore them.
        }
        $view->recordList = $recordList;
        return $view;
    }

    /**
     * Send list of ill requests to view
     *
     * @return mixed
     */
    public function illRequestsAction()
    {
        // Stop now if the user does not have valid catalog credentials available:
        if (!is_array($patron = $this->catalogLogin())) {
            return $patron;
        }

        // Connect to the ILS:
        $catalog = $this->getILS();

        // Process cancel requests if necessary:
        $cancelStatus = $catalog->checkFunction(
            'cancelILLRequests', compact('patron')
        );
        $view = $this->createViewModel();
        $view->cancelResults = $cancelStatus
            ? $this->ILLRequests()->cancelILLRequests(
                $catalog, $patron
            )
            : [];
        // If we need to confirm
        if (!is_array($view->cancelResults)) {
            return $view->cancelResults;
        }

        // By default, assume we will not need to display a cancel form:
        $view->cancelForm = false;

        // Get request details:
        $result = $catalog->getMyILLRequests($patron);
        $recordList = [];
        $this->ILLRequests()->resetValidation();
        foreach ($result as $current) {
            // Add cancel details if appropriate:
            $current = $this->ILLRequests()->addCancelDetails(
                $catalog, $current, $cancelStatus, $patron
            );
            if ($cancelStatus
                && $cancelStatus['function'] != "getCancelILLRequestLink"
                && isset($current['cancel_details'])
            ) {
                // Enable cancel form if necessary:
                $view->cancelForm = true;
            }

            // Build record driver:
            $recordList[] = $this->getDriverForILSRecord($current);
        }

        $view->recordList = $recordList;
        return $view;
    }

    /**
     * Send list of checked out books to view
     *
     * @return mixed
     */
    public function checkedoutAction()
    {
        // Stop now if the user does not have valid catalog credentials available:
        if (!is_array($patron = $this->catalogLogin())) {
            return $patron;
        }
//SCB Barcode de Amaya
//$patron['barcode']='046B1512504A80';
//var_dump($patron);
        // Connect to the ILS:
        $catalog = $this->getILS();

        // Display account blocks, if any:
        //$this->addAccountBlocksToFlashMessenger($catalog, $patron);

        //$patron = '046B1512504A80';
        // Get the current renewal status and process renewal form, if necessary:
        $renewStatus = $catalog->checkFunction('Renewals', compact('patron'));
        $renewResult = $renewStatus
            ? $this->renewals()->processRenewals(
                $this->getRequest()->getPost(), $catalog, $patron
            )
            : [];

        // By default, assume we will not need to display a renewal form:
        $renewForm = false;


        // Get checked out item details:
        //SCB. Lina
        //$patron['barcode']= '044B913A493480';
        //SCB Claudia
        //$patron['barcode']= '042F124A334680';

        $result = $catalog->getMyTransactions($patron);
/*echo '<pre>';
print_r($result);
echo '</pre>';
$result2=  $catalog->getHolding(783359);
echo '<pre>';
print_r($result2);
echo '</pre>';*/

        //COMMENTED OUT BY sb174 2017-11-09
        //$resultWithStatus=array();
        //foreach($result as $item) {
        //    $resultDriver=  $catalog->getHolding($item['id']);
        //    foreach($resultDriver as $itemDriver) {
        //        if ($itemDriver['barcode']==$item['item_id']) {
        //          $item['status'] = $itemDriver['status'];
        //        }
        //        if ($itemDriver['req_count']>0) {
        //           $item['status'] = 'Requested';
        //        }
        //        if ($itemDriver['claimsReturned']>0) {
        //           $item['status'] = 'Claims returned';
        //       }
        //    }
        //    $resultWithStatus[]=$item;
        //}
        //$result = $resultWithStatus;
        //echo "<pre>";
        //print_r($result);
        //echo '</pre>';

        // Get page size:
        $config = $this->getConfig();
        $limit = isset($config->Catalog->checked_out_page_size)
            ? $config->Catalog->checked_out_page_size : 50;

        // Build paginator if needed:
        if ($limit > 0 && $limit < count($result)) {
            $adapter = new \Zend\Paginator\Adapter\ArrayAdapter($result);
            $paginator = new \Zend\Paginator\Paginator($adapter);
            $paginator->setItemCountPerPage($limit);
            $paginator->setCurrentPageNumber($this->params()->fromQuery('page', 1));
            $pageStart = $paginator->getAbsoluteItemNumber(1) - 1;
            $pageEnd = $paginator->getAbsoluteItemNumber($limit) - 1;
        } else {
            $paginator = false;
            $pageStart = 0;
            $pageEnd = count($result);
        }

        $transactions = $hiddenTransactions = [];
        foreach ($result as $i => $current) {
            // Add renewal details if appropriate:
            $current = $this->renewals()->addRenewDetails(
               $catalog, $current, $renewStatus
            );
            if ($renewStatus && !isset($current['renew_link'])
                && $current['renewable']
            ) {
                // Enable renewal form if necessary:
                $renewForm = true;
            }

           // Build record driver (only for the current visible page):
            if ($i >= $pageStart && $i <= $pageEnd) {
                $transactions[] = $this->getDriverForILSRecord($current);
            } else {
                $hiddenTransactions[] = $current;
            }
        }

        return $this->createViewModel(
            compact(
                'transactions', 'renewForm', 'renewResult', 'paginator',
                'hiddenTransactions', 'displayItemBarcode'
            )
        );
    }

    /**
     * Send list of fines to view
     *
     * @return mixed
     */
    public function finesAction()
    {
        
        /** SCB **/
        
        $validatedFines = array();
        $feeTypes = array();
        $amount_to_pay = 0;
        
        /** SCB **/
        
        // Stop now if the user does not have valid catalog credentials available:
        if (!is_array($patron = $this->catalogLogin())) {
            return $patron;
        }

        /** SCB **/
        
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        /** SCB **/

        // Connect to the ILS:
        $catalog = $this->getILS();
        
        /** SCB **/
   
       // $patron['barcode']= '044B913A493480';
       // $patron['barcode']= '044B913A493480';
       // $patron['barcode']= '042F124A334680';
        $patron = $patron['barcode'];
        $circService=$catalog->getCirculation();
        // Get the data directly, so we have a unique id.
        $url_for_bill_id =$circService."?service=fine&patronBarcode={$patron}&operatorId=VUFIND";
        
	$request = new Request();
        $request->setMethod(Request::METHOD_GET);
        $request->setUri($url_for_bill_id);
	$client = new Client();
        $client->setOptions(array('timeout' => 230));

	try {
            $response = $client->dispatch($request);
        } catch (Exception $e) {
            throw new ILSException($e->getMessage());
        }

	$bill_data = simplexml_load_file($url_for_bill_id);
        $number_of_fines = count($bill_data->fineItem);
        $bills = array();
        for ($i = 0; $i < $number_of_fines; $i++) {
            $data = $bill_data->fineItem[$i];
            $bill = array();
            $bill['amount'] = $data->amount; // 3.00
            $bill['fine'] = $data->reason; // Service fee
            $bill['feeType'] = $data->feeType; // Service fee
            $bill['balance'] = $data->balance; // 3.00
            $bill['createdate'] = $data->billDate; // 2015-07-07 09:03:35.0
            $bill['title'] = $data->title;
            $bill['checkoutDate'] =$data->checkoutDate;
            $bill['dueDate'] = $data->dueDate;
            $bill['id'] = $data->id;
            $bill['patronBillId'] = $data->patronBillId;
	    $bills[] = $bill;
        }
        
        /** SCB **/
        

        // Get fine details:
        $result = $catalog->getMyFines($patron);
        $fines = [];
        foreach ($result as $row) {
            // Attempt to look up and inject title:
            try {
                if (!isset($row['id']) || empty($row['id'])) {
                    throw new \Exception();
                }
                $source = isset($row['source'])
                    ? $row['source'] : DEFAULT_SEARCH_BACKEND;
                $row['driver'] = $this->getServiceLocator()
                    ->get('VuFind\RecordLoader')->load($row['id'], $source);
                $row['title'] = $row['driver']->getShortTitle();
            } catch (\Exception $e) {
                if (!isset($row['title'])) {
                    $row['title'] = null;
                }
            }
            $fines[] = $row;
        }

        /** SCB **/
        //return $this->createViewModel(['fines' => $fines]);
        $view = $this->createViewModel(array('fines' => $bills));
	$user = $this->getUser();
	if(!$catalog->getPaymentsEnabledDetails()){	
		$userId = explode(',',$catalog->getIds());
		foreach($userId as $id){
			if($id==$user->username){
			        $view->paymentsEnabled = true;
			}
		}
	}else{
		  $view->paymentsEnabled = true;
	}
        $view->paymentsUrl = 'http://www.yotext.co/';
        $view->paymentsParameterName = 'text';
        $selectedFines = $this->params()->fromQuery('fines', false);
        if ($selectedFines) { // The user has pressed the button to pay fines.

            error_reporting(E_ALL);
            ini_set('display_errors', 1);

            // Don't let the user submit random bill ids. Only allow through the ones that are really there and really
            // belong to this user.
            
            foreach ($bills as $bill) {
                if (in_array($bill['id'], $selectedFines)) {
                    $amount_to_pay += floatval($bill['balance']);
                    $validatedFines[] = $bill['id'];
		    $feeTypes[] = $bill['feeType'];
		 }
            }
	    $_SESSION['amount_Paid']=$amount_to_pay;
            // Get the data we need

            // From WPM:
            $clientid = '8208';
            $pathwayId = '1751';
            $departmentId = '1';

            $cancelUrl =  $this->url()->fromRoute('myresearch-paymentcancel', array(), array('force_canonical' => true));
            $callbackUrl =  $this->url()->fromRoute('postcallback-paymentcallback', array(), array('force_canonical' => true));
            $redirectUrl =  $this->url()->fromRoute('myresearch-paymentsuccess', array(), array('force_canonical' => true));

            // We send a space separated list of bill ids. This is part of the hashed messageid, so any tampering
            // will invalidate the xml and will be rejected by the payment system. Similarly, when the response is
            // POSTed to the callback url, the same XML is supplied and the message id is validated to ensure the
            // bill ids and amount have not been altered.
            $transactionreference = implode(' ', $validatedFines);
            $feeCodeTypes = implode(',', $feeTypes);
    	    $_SESSION['transactionreference']=$transactionreference;
	    $billIDs = implode(',',$validatedFines);
    	    $_SESSION['validatedFines']=$billIDs;
	    $_SESSION['feeTypes']=$feeCodeTypes;
            $payments = array($amount_to_pay);

            $shared_secret = 'h46dhs!d6';

            

            $xmlBuilder = new WpmXmlBuilder(array(
						'barcode' => $patron,
                                                'clientId' => $clientid,
                                                'pathwayId' => $pathwayId,
                                                'departmentId' => $departmentId,
                                                'transactionReference' => $transactionreference,
                                                'payments' => $payments,
                                                'sharedSecret' => $shared_secret,
                                                'user' => $user,
                                                'redirectUrl' => $redirectUrl,
                                                'cancelUrl' => $cancelUrl,
                                                'callbackUrl' => $callbackUrl,
                                                'emailFrom' => $this->paymentEmailFrom,
                                            ));
	  
            $xmlString = $xmlBuilder->getXml();

            // The POST has to happen from the browser, so we make a form which contains the right XML and auto-submit
            // it. We can't make the XML in the browser as it needs to be signed with a shared secret.
            // Debug version. This will not auto-submit and allows the form to be inspected.
            /*$miniform = "
                <html>
                  <body onchange='document.forms[\"form\"].submit()'>
                    <form name='form' action='{$this->paymentPostUrl}' method='post'>
                        <textarea rows='20' cols='100' name='xml'>
                          {$xmlString}
                        </textarea>
                        <input type='submit' value='Submit'>
                    </form>
                  </body>
                </html>";*/
            // Auto-submit version
           $miniform = "
                <html>
                  <body onload='document.forms[\"form\"].submit()'>
                    <form name='form' action='{$this->paymentPostUrl}' method='post'>
                        <input type='hidden' name='xml' value='{$xmlString}'>
                    </form>
                  </body>
                </html>";
            

		$this->response->setContent($miniform);

            return $this->response;
        }

        return $view;
        /** SCB **/

    }

    /**
     * Convenience method to get a session initiator URL. Returns false if not
     * applicable.
     *
     * @return string|bool
     */
    protected function getSessionInitiator()
    {
        $url = $this->getServerUrl('myresearch-home');
        return $this->getAuthManager()->getSessionInitiator($url);
    }

    /**
     * Send account recovery email
     *
     * @return View object
     */
    public function recoverAction()
    {
        // Make sure we're configured to do this
        $this->setUpAuthenticationFromRequest();
        if (!$this->getAuthManager()->supportsRecovery()) {
            $this->flashMessenger()->addMessage('recovery_disabled', 'error');
            return $this->redirect()->toRoute('myresearch-home');
        }
        if ($this->getUser()) {
            return $this->redirect()->toRoute('myresearch-home');
        }
        // Database
        $table = $this->getTable('User');
        $user = false;
        // Check if we have a submitted form, and use the information
        // to get the user's information
        if ($email = $this->params()->fromPost('email')) {
            $user = $table->getByEmail($email);
        } elseif ($username = $this->params()->fromPost('username')) {
            $user = $table->getByUsername($username, false);
        }
        $view = $this->createViewModel();
        $view->useRecaptcha = $this->recaptcha()->active('passwordRecovery');
        // If we have a submitted form
        if ($this->formWasSubmitted('submit', $view->useRecaptcha)) {
            if ($user) {
                $this->sendRecoveryEmail($user, $this->getConfig());
            } else {
                $this->flashMessenger()
                    ->addMessage('recovery_user_not_found', 'error');
            }
        }
        return $view;
    }

    /**
     * Helper function for recoverAction
     *
     * @param \VuFind\Db\Row\User $user   User object we're recovering
     * @param \VuFind\Config      $config Configuration object
     *
     * @return void (sends email or adds error message)
     */
    protected function sendRecoveryEmail($user, $config)
    {
        // If we can't find a user
        if (null == $user) {
            $this->flashMessenger()->addMessage('recovery_user_not_found', 'error');
        } else {
            // Make sure we've waiting long enough
            $hashtime = $this->getHashAge($user->verify_hash);
            $recoveryInterval = isset($config->Authentication->recover_interval)
                ? $config->Authentication->recover_interval
                : 60;
            if (time() - $hashtime < $recoveryInterval) {
                $this->flashMessenger()->addMessage('recovery_too_soon', 'error');
            } else {
                // Attempt to send the email
                try {
                    // Create a fresh hash
                    $user->updateHash();
                    $config = $this->getConfig();
                    $renderer = $this->getViewRenderer();
                    $method = $this->getAuthManager()->getAuthMethod();
                    // Custom template for emails (text-only)
                    $message = $renderer->render(
                        'Email/recover-password.phtml',
                        [
                            'library' => $config->Site->title,
                            'url' => $this->getServerUrl('myresearch-verify')
                                . '?hash='
                                . $user->verify_hash . '&auth_method=' . $method
                        ]
                    );
                    $this->getServiceLocator()->get('VuFind\Mailer')->send(
                        $user->email,
                        $config->Site->email,
                        $this->translate('recovery_email_subject'),
                        $message
                    );
                    $this->flashMessenger()
                        ->addMessage('recovery_email_sent', 'success');
                } catch (MailException $e) {
                    $this->flashMessenger()->addMessage($e->getMessage(), 'error');
                }
            }
        }
    }

    /**
     * Receive a hash and display the new password form if it's valid
     *
     * @return view
     */
    public function verifyAction()
    {
        // If we have a submitted form
        if ($hash = $this->params()->fromQuery('hash')) {
            $hashtime = $this->getHashAge($hash);
            $config = $this->getConfig();
            // Check if hash is expired
            $hashLifetime = isset($config->Authentication->recover_hash_lifetime)
                ? $config->Authentication->recover_hash_lifetime
                : 1209600; // Two weeks
            if (time() - $hashtime > $hashLifetime) {
                $this->flashMessenger()
                    ->addMessage('recovery_expired_hash', 'error');
                return $this->forwardTo('MyResearch', 'Login');
            } else {
                $table = $this->getTable('User');
                $user = $table->getByVerifyHash($hash);
                // If the hash is valid, forward user to create new password
                if (null != $user) {
                    $this->setUpAuthenticationFromRequest();
                    $view = $this->createViewModel();
                    $view->auth_method
                        = $this->getAuthManager()->getAuthMethod();
                    $view->hash = $hash;
                    $view->username = $user->username;
                    $view->useRecaptcha
                        = $this->recaptcha()->active('changePassword');
                    $view->setTemplate('myresearch/newpassword');
                    return $view;
                }
            }
        }
        $this->flashMessenger()->addMessage('recovery_invalid_hash', 'error');
        return $this->forwardTo('MyResearch', 'Login');
    }

    /**
     * Reset the new password form and return the modified view. When a user has
     * already been loaded from an existing hash, this resets the hash and updates
     * the form so that the user can try again.
     *
     * @param mixed     $userFromHash User loaded from database, or false if none.
     * @param ViewModel $view         View object
     *
     * @return ViewModel
     */
    protected function resetNewPasswordForm($userFromHash, ViewModel $view)
    {
        if ($userFromHash) {
            $userFromHash->updateHash();
            $view->username = $userFromHash->username;
            $view->hash = $userFromHash->verify_hash;
        }
        return $view;
    }

    /**
     * Handling submission of a new password for a user.
     *
     * @return view
     */
    public function newPasswordAction()
    {
        // Have we submitted the form?
        if (!$this->formWasSubmitted('submit')) {
            return $this->redirect()->toRoute('home');
        }
        // Pull in from POST
        $request = $this->getRequest();
        $post = $request->getPost();
        // Verify hash
        $userFromHash = isset($post->hash)
            ? $this->getTable('User')->getByVerifyHash($post->hash)
            : false;
        // View, password policy and reCaptcha
        $view = $this->createViewModel($post);
        $view->passwordPolicy = $this->getAuthManager()
            ->getPasswordPolicy();
        $view->useRecaptcha = $this->recaptcha()->active('changePassword');
        // Check reCaptcha
        if (!$this->formWasSubmitted('submit', $view->useRecaptcha)) {
            $this->setUpAuthenticationFromRequest();
            return $this->resetNewPasswordForm($userFromHash, $view);
        }
        // Missing or invalid hash
        if (false == $userFromHash) {
            $this->flashMessenger()->addMessage('recovery_user_not_found', 'error');
            // Force login or restore hash
            $post->username = false;
            return $this->forwardTo('MyResearch', 'Recover');
        } elseif ($userFromHash->username !== $post->username) {
            $this->flashMessenger()
                ->addMessage('authentication_error_invalid', 'error');
            return $this->resetNewPasswordForm($userFromHash, $view);
        }
        // Verify old password if we're logged in
        if ($this->getUser()) {
            if (isset($post->oldpwd)) {
                // Reassign oldpwd to password in the request so login works
                $tempPassword = $post->password;
                $post->password = $post->oldpwd;
                $valid = $this->getAuthManager()->validateCredentials($request);
                $post->password = $tempPassword;
            } else {
                $valid = false;
            }
            if (!$valid) {
                $this->flashMessenger()
                    ->addMessage('authentication_error_invalid', 'error');
                $view->verifyold = true;
                return $view;
            }
        }
        // Update password
        try {
            $user = $this->getAuthManager()->updatePassword($this->getRequest());
        } catch (AuthException $e) {
            $this->flashMessenger()->addMessage($e->getMessage(), 'error');
            return $view;
        }
        // Update hash to prevent reusing hash
        $user->updateHash();
        // Login
        $this->getAuthManager()->login($this->request);
        // Go to favorites
        $this->flashMessenger()->addMessage('new_password_success', 'success');
        return $this->redirect()->toRoute('myresearch-home');
    }

    /**
     * Handling submission of a new password for a user.
     *
     * @return view
     */
    public function changePasswordAction()
    {
        if (!$this->getAuthManager()->isLoggedIn()) {
            return $this->forceLogin();
        }
        // If not submitted, are we logged in?
        if (!$this->getAuthManager()->supportsPasswordChange()) {
            $this->flashMessenger()->addMessage('recovery_new_disabled', 'error');
            return $this->redirect()->toRoute('home');
        }
        $view = $this->createViewModel($this->params()->fromPost());
        // Verify user password
        $view->verifyold = true;
        // Display username
        $user = $this->getUser();
        $view->username = $user->username;
        // Password policy
        $view->passwordPolicy = $this->getAuthManager()
            ->getPasswordPolicy();
        // Identification
        $user->updateHash();
        $view->hash = $user->verify_hash;
        $view->setTemplate('myresearch/newpassword');
        $view->useRecaptcha = $this->recaptcha()->active('changePassword');
        return $view;
    }

    /**
     * Helper function for verification hashes
     *
     * @param string $hash User-unique hash string from request
     *
     * @return int age in seconds
     */
    protected function getHashAge($hash)
    {
        return intval(substr($hash, -10));
    }

    /**
     * Configure the authentication manager to use a user-specified method.
     *
     * @return void
     */
    protected function setUpAuthenticationFromRequest()
    {
        $method = trim(
            $this->params()->fromQuery(
                'auth_method', $this->params()->fromPost('auth_method')
            )
        );
        if (!empty($method)) {
            $this->getAuthManager()->setAuthMethod($method);
        }
    }
    
    /** SCB **/
    
    /**
     * Payment cancelation action
     *
     * @return mixed
     */
    public function paymentcancelAction()
    {
        $this->flashMessenger()->setNamespace('info')->addMessage('payment_cancel');
        return $this->redirect()->toRoute('myresearch-fines');
    }

    /**
     * Payment success action
     *
     * @return mixed
     */
    public function paymentsuccessAction()
    {
	if (!is_array($patron = $this->catalogLogin())) {
            return $patron;
        }
		
	$this->flashMessenger()->setNamespace('info')->addMessage('payment_success');
        return $this->redirect()->toRoute('myresearch-fines');
    }

    
    /**
     * This is the action that the payments system will hit when the payment is complete. It is essential that
     * there is no authentication here. It will validate the XML and pay the fines if the payment succeeded.
     */
    public function paymentcallbackAction() {
	$catalog = $this->getILS();
        $db=$catalog->getConnection();
        $post_xml = file_get_contents("php://input");
	//dump_to_tmp_file($post_xml);   //TG3 290151014
	$validator = new WpmXmlValidator($post_xml, 'h46dhs!d6');
        if ($validator->valid()) {
            if ($validator->isPaid()) {
		$transId=$validator->transId();
		$dop=$validator->paymentDate();
		$totalAmt=$validator->amtPaid();
		$billIds=$validator->billIds();
		$bills=implode(',',$billIds);
		$patron=$validator->barcode();
		$circService=$catalog->getCirculation();
		$uri = $circService.'?service=finePayment&paymentType=credit card&patronBarcode='.$patron.'&operatorId=VUFIND&transcationReference='.$transId.'&fineType=OVR_DUE&billIds='.$bills.'&amountPaid='.$totalAmt.'&responseFormatType=JSON';
               	$responseString=simplexml_load_file($uri);
		$pos = strpos($responseString, "successfully");
  		if ($pos == false) {
			$sql= "insert into OLE_VUFIND_PTRN_PAYMENT_LOG(PTRN_BARCODE,PAY_SYS_TRANS_ID,FEE_TYPE,PYMT_TYP,BILL_IDS,AMT_PAID,ISPAID,PAY_SYS_MESSAGE,PAY_DT_TM,UPTD_OLE_STAS,REC_DT_TM) values('".$patron."','".$transId."',' ','credit card','".$bills."',".$totalAmt.",'YES',' ','".$dop."','NO',now())";
                	try {
                    		$sqlStmt = $db->prepare($sql);
                    		$sqlStmt->execute();
                	}
                	catch (PDOException $e) {
                    		throw new ILSException($e->getMessage());
                	}
			$this->send_failure_response($validator);
	    	}else{
			$sql= "insert into OLE_VUFIND_PTRN_PAYMENT_LOG(PTRN_BARCODE,PAY_SYS_TRANS_ID,FEE_TYPE,PYMT_TYP,BILL_IDS,AMT_PAID,ISPAID,PAY_SYS_MESSAGE,PAY_DT_TM,UPTD_OLE_STAS,REC_DT_TM) values('".$patron."','".$transId."',' ','credit card','".$bills."',".$totalAmt.",'YES',' ','".$dop."','YES',now())";
                        try {
                                $sqlStmt = $db->prepare($sql);
                                $sqlStmt->execute();
                        }
                        catch (PDOException $e) {
                                throw new ILSException($e->getMessage());
                        }
			$this->send_success_response($validator);
		}
             } else {
	     	$transId=$validator->transId();
             	$dop=$validator->paymentDate();
             	$totalAmt=$validator->amtPaid();
             	$billIds=$validator->billIds();
             	$bills=implode(',',$billIds);
             	$patron=$validator->barcode();
	     	$wpmSysMsg=$validator->failureReason();
	     	$sql= "insert into OLE_VUFIND_PTRN_PAYMENT_LOG(PTRN_BARCODE,PAY_SYS_TRANS_ID,FEE_TYPE,PYMT_TYP,BILL_IDS,AMT_PAID,ISPAID,PAY_SYS_MESSAGE,PAY_DT_TM,UPTD_OLE_STAS,REC_DT_TM) values('".$patron."','".$transId."',' ','credit card','".$bills."',".$totalAmt.",'NO','Not updated in WPM".$wpmSysMsg."','".$dop."','NO',now())";
                try {
                    $sqlStmt = $db->prepare($sql);
                    $sqlStmt->execute();
                }
                catch (PDOException $e) {
                    throw new ILSException($e->getMessage());
                }

            	// send failure response
            	$this->send_failure_response($validator);
           }
	}               
        $this->flashMessenger()->setNamespace('info')->addMessage('payment_success');
        return $this->redirect()->toRoute('myresearch-fines');
    }

    /**
     *  pes out the file in /tmp so we can see what the callback is doing.
     *
     * @param $data
     */
    protected function wipe_tmp_file() {
        $myFile = "/tmp/vufind_tester.txt";
        $fh = fopen($myFile, 'w');
        fwrite($fh, "");
        fclose($fh);
    }

    /**
     * Writes something to a file in /tmp so we can see what the callback is doing.
     *
     * @param $data
     */
    protected function dump_to_tmp_file($data) {
        $myFile = "/tmp/vufind_tester.txt";
        $fh = fopen($myFile, 'a');
        fwrite($fh, (string)$data);
        fclose($fh);
    }

    /**
     * @param \mysqli $mysqli
     * @param int $billId
     * @param string $date 2012-11-20 09:53:14
     */
    protected function payBill($mysqli, $billId, $date) {

        // Get the amount paid for this bill. There is no option to pay part of the bill.
        $amount_paid = '';
        // Convert the string to numbers.
        $date = strtotime($date);

        // We have to use bind params method as the server does not have the mysql native driver.
        $sql = "SELECT UNPAID_BAL
                  FROM ole.ole_dlvr_ptrn_bill_t
                 WHERE PTRN_BILL_ID = {$billId} LIMIT 1";
        if (!($stmt = $mysqli->prepare($sql))) {
            echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
        }
        if (!$stmt->execute()) {
            echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
        }
        $stmt->bind_result($amount_paid);
        $stmt->fetch();
        $stmt->store_result();
        // We should now have the amount. If many bills have been paid, there will be many small amounts,
        // so we don't use the total that comes back from the payment system - just the ids that were sent and
        // then sent back.
//mysqli_report(MYSQLI_REPORT_ALL);
//mysqli_report(MYSQLI_REPORT_ALL ^ MYSQLI_REPORT_INDEX);

        // Mark the bill as paid
        $sql = "UPDATE ole.ole_dlvr_ptrn_bill_t bill
                   SET bill.VER_NBR = bill.VER_NBR+1,
                       bill.UNPAID_BAL = 0,
                       bill.PAY_METHOD_ID = 'Credit Card',
                       bill.PAY_AMT = {$amount_paid},
                       bill.PAY_DT=FROM_UNIXTIME({$date}),
                       bill.PAY_OPTR_ID='WPM',
                       bill.PAY_NOTE='{$amount_paid} paid through Credit Card'
                 WHERE bill.PTRN_BILL_ID={$billId}";
        if (!($stmt = $mysqli->prepare($sql))) {
            echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
        }
        if (!$stmt->execute()) {
            echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
        }
        $stmt->store_result(); 

        $sql = "UPDATE ole.ole_dlvr_ptrn_bill_fee_typ_t fee
             LEFT JOIN ole.ole_dlvr_ptrn_bill_t bill
                    ON bill.PTRN_BILL_ID = fee.PTRN_BILL_ID
                   SET fee.PAY_STATUS_ID = '2',
                       fee.BALANCE_AMT = fee.BALANCE_AMT-{$amount_paid}
                 WHERE bill.PTRN_BILL_ID = {$billId}";
        if (!($stmt = $mysqli->prepare($sql))) {
            echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
        }
        if (!$stmt->execute()) {
            echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
        }
        $stmt->store_result();
       
         $sql = "INSERT INTO ole.ole_dlvr_ptrn_bill_pay_t (ID, ITM_LINE_ID, BILL_PAY_AMT, CRTE_DT_TIME, OPTR_CRTE_ID, TRNS_MODE)
                 SELECT trans.PTRN_BILL_ID, bill.ID, trans.PAY_AMT, trans.CRTE_DT_TIME, trans.OPTR_CRTE_ID, trans.PAY_METHOD_ID
                   FROM ole.ole_dlvr_ptrn_bill_t trans 
                    INNER JOIN ole.ole_dlvr_ptrn_bill_fee_typ_t bill
                   ON trans.PTRN_BILL_ID = bill.PTRN_BILL_ID
                  WHERE trans.PTRN_BILL_ID={$billId}";
        if (!($stmt = $mysqli->prepare($sql))) {
            echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
        }
        if (!$stmt->execute()) {
            echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
        }
        $stmt->store_result();
    }

    /**
     * @param $validator
     */
    protected function pay_all_fines($validator) {
        //$mysqli = new \mysqli("william.lis.soas.ac.uk", "OLE", "OLE", "ole");
        $mysqli = new \mysqli($this->finesDbHost, $this->finesDbUser, $this->finesDbPass, $this->finesDbName);
        if ($mysqli->connect_errno) {
            echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
        }

        // Pay fines
        foreach ($validator->billIds() as $billId) {
            $this->payBill($mysqli, $billId, $validator->paymentDate());
        }
        $mysqli->close();
    }

    /**
     * @param $validator
     */
    protected function send_success_response($validator) {
        $success_return ='<?xml version="1.0" encoding="utf-8"?><wpmmessagevalidation msgid='.$validator->sentMessageId().'">
                <validation>1</validation>
                <validationmessage><![CDATA[Success]]></validationmessage>
                </wpmmessagevalidation>';

               //echo response on page so WPM pick it up.
                echo $success_return;
    }

    /**
     * @param $validator
     */
    protected function send_failure_response($validator) {
        $failure_return ='<?xml version="1.0" encoding="utf-8"?><wpmmessagevalidation msgid="'.$validator->sentMessageId().'">
                <validation>0</validation>
                <validationmessage><![CDATA[Failure]]></validationmessage>
                </wpmmessagevalidation>';

               //echo response on page so WPM pick it up.
                echo $failure_return;
    }
    
    /** SCB **/
    
}
