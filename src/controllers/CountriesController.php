<?php

namespace craft\commerce\controllers;

use Craft;
use craft\commerce\models\Country;
use craft\commerce\Plugin;
use yii\web\HttpException;

/**
 * Class Countries Controller
 *
 * @author    Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @copyright Copyright (c) 2015, Pixel & Tonic, Inc.
 * @license   https://craftcommerce.com/license Craft Commerce License Agreement
 * @see       https://craftcommerce.com
 * @package   craft.plugins.commerce.controllers
 * @since     1.0
 */
class CountriesController extends BaseAdminController
{
    /**
     * @throws HttpException
     */
    public function actionIndex()
    {
        $countries = Plugin::getInstance()->getCountries()->getAllCountries();
        $this->renderTemplate('commerce/settings/countries/index',
            compact('countries'));
    }

    /**
     * Create/Edit Country
     *
     * @param array $variables
     *
     * @throws HttpException
     */
    public function actionEdit(array $variables = [])
    {
        if (empty($variables['country'])) {
            if (!empty($variables['id'])) {
                $id = $variables['id'];
                $variables['country'] = Plugin::getInstance()->getCountries()->getCountryById($id);

                if (!$variables['country']) {
                    throw new HttpException(404);
                }
            } else {
                $variables['country'] = new Country();
            }
        }

        if (!empty($variables['id'])) {
            $variables['title'] = $variables['country']->name;
        } else {
            $variables['title'] = Craft::t('commerce', 'Create a new country');
        }

        $this->renderTemplate('commerce/settings/countries/_edit', $variables);
    }

    /**
     * @throws HttpException
     */
    public function actionSave()
    {
        $this->requirePostRequest();

        $country = new Country();

        // Shared attributes
        $country->id = Craft::$app->getRequest()->getParam('countryId');
        $country->name = Craft::$app->getRequest()->getParam('name');
        $country->iso = Craft::$app->getRequest()->getParam('iso');
        $country->stateRequired = Craft::$app->getRequest()->getParam('stateRequired');

        // Save it
        if (Plugin::getInstance()->getCountries()->saveCountry($country)) {
            Craft::$app->getSession()->setNotice(Craft::t('commerce', 'Country saved.'));
            $this->redirectToPostedUrl($country);
        } else {
            Craft::$app->getSession()->setError(Craft::t('commerce', 'Couldn’t save country.'));
        }

        // Send the model back to the template
        Craft::$app->getUrlManager()->setRouteParams(['country' => $country]);
    }

    /**
     * @throws HttpException
     */
    public function actionDelete()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $id = Craft::$app->getRequest()->getRequiredParam('id');

        try {
            Plugin::getInstance()->getCountries()->deleteCountryById($id);
            $this->asJson(['success' => true]);
        } catch (\Exception $e) {
            $this->asErrorJson($e->getMessage());
        }
    }

}