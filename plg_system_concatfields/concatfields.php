<?php
/**
 * @package    ConcatFields
 *
 * @author     Peter Martin <joomla@db8.nl>
 * @copyright  Copyright 2024-2025 by Peter Martin
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link       https://db8.nl
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

/**
 * Class for ConcatFields
 *
 * @since  1.0.0
 */
class PlgSystemConcatfields extends CMSPlugin
{
    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  1.0.0
     */
    protected $autoloadLanguage = true;

    /**
     * Application object.
     *
     * @var    CMSApplication
     * @since  1.0.0
     */
    protected $app;

    /**
     * Database object.
     *
     * @var    DatabaseDriver
     * @since  1.0.0
     */
    protected $db;

    /**
     * The save event.
     *
     * @param string $context The context
     * @param Table $item The table
     * @param boolean $isNew Is new item
     * @param array $data The validated data
     *
     * @return  void
     *
     * @since   3.7.0
     */
    public function onContentAfterSave($context, $item, $isNew, $data = [])
    {
        // Check if data is an array and the item has an id
        if (!\is_array($data) || empty($item->id) || empty($data['com_fields'])) {
            return;
        }

        if ($context !== 'com_content.article') {
            return;
        }

        // Change these Custom Field names
        $customField1 = 'name-of-custom-field1';
        $customField2 = 'name-of-custom-field2';
        $customField3 = 'name-of-custom-field3'; // Concatenate Field1 + Field2 into this Field3

        if (!isset($data['com_fields'][$customField1]) || empty($data['com_fields'][$customField1])
            || !isset($data['com_fields'][$customField2]) || empty($data['com_fields'][$customField2])) {
            return;
        }

        // Construct the concatenated URL
        $concatenatedFields = '<a href="' . htmlspecialchars($data['com_fields'][$customField1], ENT_QUOTES, 'UTF-8') . '" class="button">'
            . htmlspecialchars($data['com_fields'][$customField2], ENT_QUOTES, 'UTF-8') . '</a>';

        // Save the fullUrl value to the corresponding custom field
        $fieldId = $this->getFieldIdByName($customField3);

        if ($fieldId) {
            try {
                $this->saveFieldValue($fieldId, $item->id, $concatenatedFields);
                return;
            } catch (Exception $e) {
                $this->app->enqueueMessage('Error saving custom field value: ' . $e->getMessage(), 'error');
            }
        } else {
            $this->app->enqueueMessage('Custom field "' . $customField3 . '" not found.', 'error');
        }

        return;
    }

    /**
     * Get the field ID by field name.
     *
     * @param string $fieldName The name of the custom field
     *
     * @return int|null The ID of the custom field, or null if not found
     */
    private function getFieldIdByName(string $fieldName): ?int
    {
        $db = $this->db;
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__fields'))
            ->where($db->quoteName('name') . ' = ' . $db->quote($fieldName))
            ->where($db->quoteName('context') . ' = ' . $db->quote('com_content.article'));
        return $db->setQuery($query)->loadResult();
    }

    /**
     * Save the value of a custom field.
     *
     * @param int $fieldId The ID of the custom field
     * @param string $itemId The ID of the article
     * @param string $value The value to save
     *
     * @return void
     */
    private function saveFieldValue(int $fieldId, string $itemId, string $value)
    {
        // Check if a value already exists for the field
        $db = $this->db;
        $query = $db->getQuery(true)
            ->select($db->quoteName('value'))
            ->from($db->quoteName('#__fields_values'))
            ->where($db->quoteName('field_id') . ' = ' . $fieldId)
            ->where($db->quoteName('item_id') . ' = ' . $db->quote($itemId));
        $db->setQuery($query);
        $valueResult = $db->loadResult();

        if ($valueResult) {
            // Update the existing value
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__fields_values'))
                ->set($db->quoteName('value') . ' = ' . $db->quote($value))
                ->where($db->quoteName('field_id') . ' = ' . $fieldId)
                ->where($db->quoteName('item_id') . ' = ' . (int)$itemId);
        } else {
            // Insert a new value
            $query = $db->getQuery(true)
                ->insert($db->quoteName('#__fields_values'))
                ->columns([$db->quoteName('field_id'), $db->quoteName('item_id'), $db->quoteName('value')])
                ->values((int)$fieldId . ', ' . (int)$itemId . ', ' . $db->quote($value));
        }
        $db->setQuery($query);
        $db->execute();
    }
}
