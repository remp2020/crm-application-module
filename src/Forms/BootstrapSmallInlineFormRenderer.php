<?php

namespace Crm\ApplicationModule\Forms;

use Nette\Forms\Controls\Checkbox;
use Nette\Forms\Controls\CheckboxList;
use Nette\Forms\Controls\MultiSelectBox;
use Nette\Forms\Controls\RadioList;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Controls\TextBase;
use Nette\Forms\Form;
use Nette\Forms\Rendering\DefaultFormRenderer;

class BootstrapSmallInlineFormRenderer extends DefaultFormRenderer
{
    public array $wrappers = [
        'form' => [
            'container' => '',
        ],
        'error' => [
            'container' => 'ul class=error',
            'item' => 'li',
        ],
        'group' => [
            'container' => 'fieldset',
            'label' => 'legend',
            'description' => 'p',
        ],
        'controls' => [
            'container' => '',
        ],
        'pair' => [
            'container' => 'div class=form-group',
            '.required' => 'required',
            '.optional' => null,
            '.odd' => null,
            '.error' => 'has-error',
        ],
        'control' => [
            'container' => null,
            '.odd' => null,
            'description' => 'span class=help-block',
            'requiredsuffix' => '',
            'errorcontainer' => 'span class=help-block',
            'erroritem' => '',
            '.required' => 'required',
            '.text' => 'text',
            '.password' => 'text',
            '.file' => 'text',
            '.submit' => 'button',
            '.image' => 'imagebutton',
            '.button' => 'button',
        ],
        'label' => [
            'container' => '',
            'suffix' => null,
            'requiredsuffix' => '',
        ],
        'hidden' => [
            'container' => 'div',
        ],
    ];

    /**
     * Provides complete form rendering.
     * @param string $mode 'begin', 'errors', 'ownerrors', 'body', 'end' or empty to render all
     * @return string
     */
    public function render(Form $form, string $mode = null): string
    {
        $form->getElementPrototype()->appendAttribute('class', 'form-inline');

        foreach ($form->getControls() as $control) {
            if ($control instanceof TextBase ||
                $control instanceof SelectBox ||
                $control instanceof MultiSelectBox) {
                $control->getControlPrototype()->addClass('form-control');
            } elseif ($control instanceof Checkbox ||
                $control instanceof CheckboxList ||
                $control instanceof RadioList) {
                $control->getSeparatorPrototype()->setName('div')->addClass($control->getControlPrototype()->type);
            }
        }

        return parent::render($form, $mode);
    }
}
