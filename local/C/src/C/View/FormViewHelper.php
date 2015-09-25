<?php
namespace C\View;

use Symfony\Component\Form\FormView;

// @todo import from SF/Silex.
class FormViewHelper extends AbstractViewHelper {

    /**
     * @var CommonViewHelper
     */
    public $commons;
    public function setCommonHelper (CommonViewHelper $helper) {
        $this->commons = $helper;
    }


    // form
    // vendor/symfony/twig-bridge/Extension/FormExtension.php
    // vendor/symfony/twig-bridge/Resources/views/Form/form_div_layout.html.twig
    public function form(FormView $form) {
        $str = "";
        $str .= $this->form_start($form);
        $str .= $this->form_widget($form);
        $str .= $this->form_end($form);
        return $str;
    }
    public function form_start (FormView $form, $variables=[]) {
        $vars = array_merge_recursive($form->vars, $variables);

        $method = $this->commons->upper($vars['method']);
        $name = ($vars['name']);
        $action = ($vars['action']);
        $attr = ($vars['attr']);
        $form_method = '';

        if (in_array($method, ['GET', 'POST',])) {
            $form_method = $method;
        } else {
            $form_method = 'POST';
        }

        $str = "";
        $str .= "<form name='$name' method='".$this->commons->lower($form_method)."' action='$action' ";
        foreach ($attr as $name=>$val) {
            $str .= " $attr='$val' ";
        }
        $str .= $this->form_enctype($form, $variables);
        $str .= " >";
        if ($form_method!==$method) {
            $str .= "<input type='hidden' name='_method' value='$method' />";
        }
        return $str;
    }
    public function form_widget (FormView $form, $variables=[]) {
        $str = "";
        $vars = array_merge_recursive($form->vars, $variables);
        $compound = ($vars['compound']);

        if ($compound) {
            $str .= $this->form_widget_compound($form, $variables);
        } else {
            $str .= $this->form_widget_simple($form, $variables);
        }
        return $str;
    }
    public function form_widget_compound (FormView $form, $variables=[]) {
        $str = "";
        $str .= "<div";
        $str .= $this->widget_container_attributes($form, $variables);
        if (!$form->parent) {
            $str .= $this->form_errors($form);
        }
        $str .= ">";
        $str .= $this->form_rows($form, $variables);
        $str .= "</div>";
        return $str;
    }
    public function form_widget_simple (FormView $form, $variables=[]) {
        $str = "";
        $vars = array_merge_recursive($form->vars, $variables);
        $type = $vars['type'] ? $vars['type'] : 'text';
        $val = $vars['value'];
        $str .= "<input ";
        $str .= "type='$type' ";
        $str .= $this->widget_attributes($form, $variables);
        if ($val) {
            $str .= "value='$val' ";
        }
        $str .= "/>";
        return $str;
    }
    public function widget_attributes (FormView $form, $variables=[]) {
        $str = "";
        $vars = array_merge_recursive($form->vars, $variables);
        $id = ($vars['id']);
        $full_name = ($vars['full_name']);
        $readonly = ($vars['read_only']);
        $disabled = ($vars['disabled']);
        $required = ($vars['required']);
        $attr = ($vars['attr']);
        $str .= "id='$id' ";
        $str .= "name='$full_name' ";
        if ($readonly && !array_key_exists('readonly', $attr)) $str .= "readonly='readonly'";
        if ($disabled) $str .= "disabled='disabled'";
        if ($required) $str .= "required='required'";
        foreach ($attr as $name=>$val) {
            if (in_array($name, ['placeholder', 'title',])) {
                $str .= " $attr='$val' "; // here should do translation.
            } else if ($val==true) { // triple eq ? or double eq ?
                $str .= " $attr='$attr' ";
            } else if ($val!=false) { // triple eq ? or double eq ?
                $str .= " $attr='$val' ";
            }
        }
        return $str;
    }
    public function widget_container_attributes (FormView $form, $variables=[]) {
        $str = "";
        $vars = array_merge_recursive($form->vars, $variables);
        $id = ($vars['id']);
        $attr = ($vars['attr']);

        if ($id) {
            $str .= "id='$id'";
        }
        foreach ($attr as $name=>$val) {
            if (in_array($name, ['placeholder', 'title',])) {
                $str .= " $attr='$val' "; // here should do translation.
            } else if ($val==true) { // triple eq ? or double eq ?
                $str .= " $attr='$attr' ";
            } else if ($val!=false) { // triple eq ? or double eq ?
                $str .= " $attr='$val' ";
            }
        }
        return $str;
    }

    public function form_end (FormView $form, $variables=[]) {
        $str = "";
        $vars = array_merge_recursive($form->vars, $variables);
        if (!array_key_exists('render_rest', $vars) || $vars['render_rest']) {
            $str .= $this->form_rest($form);
        }
        $str .= "</form>";
        return $str;
    }

    public function form_enctype (FormView $form, $variables=[]) {
        $str = "";
        $vars = array_merge_recursive($form->vars, $variables);
        $multipart = ($vars['multipart']);
        if ($multipart) {
            $str .= " enctype='multipart/form-data' ";
        }
        return $str;
    }

    public function form_errors (FormView $form, $variables=[]) {
        $str = "";
        $vars = array_merge_recursive($form->vars, $variables);
        $errors = $vars['errors'];
        if (count($errors)) {
            $str .= "<ul>";
            foreach ($errors as $error) {
                $str .= "<li>";
                $str .= $error->message;
                $str .= "</ul>";
            }
            $str .= "</ul>";
        }
        return $str;
    }

    public function form_label (FormView $form, $label=null, $variables=[]) {
        $str = "";
        $vars = array_merge_recursive($form->vars, $variables);
        $label = $label?$label:$vars['label'];
        $id = $vars['id'];
        $compound = $vars['compound'];
        $label_attr = $vars['label_attr'];
        $name = $vars['name'];
        $required = $vars['required'];
        $label_format = $vars['label_format'];
        $translation_domain = $vars['translation_domain'];

        if ($label!==false) {
            if ($compound!==false) {
                $label_attr = array_merge(['for'=>$id], $label_attr);
            }
            if ($required) {
                $label_attr = array_merge(['class' => ''], $label_attr);
                $label_attr['class'] .= ' required';
            }

            if (!$label) {
                if ($label_format) {
                    $label = $this->commons->format($label_format, [
                        '%name%'=>$name,
                        '%id%'  =>$id,
                    ]);
                } else {
                    // @todo check with twig original
                    $str .= $this->commons->humanize($name);
                }
            }

            $str .= "<label ";
            foreach ($label_attr as $name=>$val) {
                $str .= "$name='$val' ";
            }
            $str .= ">";
            $str .= $translation_domain
                ? $this->commons->trans($label)  // @todo check with original twig.
                : $label ;
            $str .= "</label>";
        }
        return $str;
    }
    public function form_row (FormView $form, $variables=[]) {
        $str = "";
        $str .= "<div>";
        $str .= $this->form_label($form, null, $variables);
        $str .= $this->form_errors($form, $variables);
        $str .= $this->form_widget($form, $variables);
        $str .= "</div>";
        return $str;
    }
    public function form_rows (FormView $form, $variables=[]) {
        $str = "";
        foreach ($form->children as $child) {
            $str .= $this->form_row($child, $variables);
        }
        return $str;
    }
    public function form_rest (FormView $form, $variables=[]) {
        $str = "";
        foreach ($form->children as $child) {
            if (!$child->isRendered()) {
                $str .= $this->form_row($child, $variables);
            }
        }
        return $str;
    }
    public function collection_widget (FormView $form, $variables=[]) {
        /*
         * @todo see what to do with it and original implementation.
{%- block collection_widget -%}
    {% if prototype is defined %}
        {%- set attr = attr|merge({'data-prototype': form_row(prototype) }) -%}
    {% endif %}
    {{- block('form_widget') -}}
{%- endblock collection_widget -%}
         */
        $str = "";
        $str .= $this->form_widget ($form, $variables);
        return $str;
    }
    public function textarea_widget (FormView $form, $variables=[]) {
        $str = "";
        $vars = array_merge_recursive($form->vars, $variables);
        $val = $vars['value'];
        $str .= "<textarea ";
        $str .= $this->widget_attributes($form, $variables);
        $str .= ">";
        $str .= "$val";
        $str .= "</textarea>";
        return $str;
    }
    public function choice_widget (FormView $form, $variables=[]) {
        $str = "";
        $vars = array_merge_recursive($form->vars, $variables);
        $expanded = isset($vars['expanded'])?$vars['expanded']:false;
        if ($expanded) {
            $str .= $this->choice_widget_expanded ($form, $variables);
        } else {
            $str .= $this->choice_widget_collapsed ($form, $variables);
        }
        return $str;
    }
    public function choice_widget_expanded (FormView $form, $variables=[]) {
        $str = "";
        $str .= "<div ".$this->widget_container_attributes($form, $variables).">";
        $str .= $this->form_widget($form, $variables);
        $str .= $this->form_label($form, null, $variables);
        $str .= "</div>";
        return $str;
    }
    public function choice_widget_collapsed (FormView $form, $variables=[]) {
        $str = "";
        $vars = array_merge_recursive($form->vars, $variables);
        $value = isset($vars['value'])?$vars['value']:false;
        $required = isset($vars['required'])?$vars['required']:null;
        $placeholder = isset($vars['placeholder'])?$vars['placeholder']:false;
        $placeholder_in_choices = isset($vars['placeholder_in_choices'])?$vars['placeholder_in_choices']:false;
        $preferred_choices = isset($vars['preferred_choices'])?$vars['preferred_choices']:[];
        $choices = isset($vars['choices'])?$vars['choices']:[];
        $separator = isset($vars['separator'])?$vars['separator']:"";
        $multiple = isset($vars['multiple'])?$vars['multiple']:false;

        /*
         * @todo check behavior with original.
    {%- if required and placeholder is none and not placeholder_in_choices and not multiple -%}
        {% set required = false %}
    {%- endif -%}
         */
        if ($required===null && !$placeholder_in_choices && !$required) {
            $required = false;
        }

        $str .= "<select ".$this->widget_attributes($form, $vars)." ";
        $str .= $multiple?"multiple='multiple'":"";
        $str .= ">";
        if ($placeholder) {
            $str .= "<option ";
            if ($required && !$value) {
                $str .= "selected='selected' ";
            }
            $str .= ">";
            $str .= "$placeholder"; // @todo translation

        }

        if (count($preferred_choices)) {
            $variables['options'] = $preferred_choices;
            $this->choice_widget_options($form, $variables);
            if (count($choices) && $separator) {
                $str .= "<option disabled='disabled'>$separator</option>";

            }
        }
        $variables['options'] = $choices;
        $str .= $this->choice_widget_options ($form,$variables);
        $str .= "</select>";

        return $str;
    }
    public function choice_widget_options (FormView $form, $variables=[]) {
        $str = "";
        $vars = array_merge_recursive($form->vars, $variables);
        $options = isset($vars['options'])?$vars['options']:[];
        $choice_translation_domain = isset($vars['choice_translation_domain'])?$vars['choice_translation_domain']:[];

        foreach ($options as $group_label => $choice) {
            if ($choice instanceof \Traversable || is_array($choice)) {
                $str .= "<optgroup label='";
                $str .= !$choice_translation_domain
                    ? $this->commons->trans($group_label)
                    : $this->commons->trans($group_label, $choice_translation_domain);
                $str .= "' ";
                $str .= $this->choice_widget_options($form, ['options'=>$choice]);
                $str .= "</optgroup>";
            } else {
                $choiceLabel = $choice['label'];
                $choiceValue = $choice['value'];
                $selected_value = isset($choice['selected_value']) ? $choice['selected_value'] : "";
                $str .= "<option value='$choiceValue' ";
                $str .= $this->widget_attributes($choice); // @todo check it, coz original code called block('attributes')
                if ($choiceValue===$selected_value || in_array($choiceValue, $selected_value)) {
                    $str .= "selected='selected' ";
                }
                $str .= ">";
                $str .= !$choice_translation_domain
                    ? $this->commons->trans($choiceLabel)
                    : $this->commons->trans($choiceLabel, $choice_translation_domain);
                $str .= "</option>";
            }
        }
        return $str;
    }
    public function checkbox_widget (FormView $form, $variables=[]) {
        $str = "";
        $vars = array_merge_recursive($form->vars, $variables);
        $value = isset($vars['value'])?$vars['value']:null;
        $checked = isset($vars['checked'])?$vars['checked']:null;

        $str .= "<input type='checkbox' ";
        $str .= $this->widget_attributes ($form, $variables);
        if ($value) {
            $str .= "value='$value' ";
        }
        if ($checked) {
            $str .= "checked='checked' ";
        }
        $str .= " />";
        return $str;
    }
    public function radio_widget (FormView $form, $variables=[]) {
        $str = "";
        $vars = array_merge_recursive($form->vars, $variables);
        $value = isset($vars['value'])?$vars['value']:null;
        $checked = isset($vars['checked'])?$vars['checked']:null;

        $str .= "<input type='radio' ";
        $str .= $this->widget_attributes ($form, $variables);
        if ($value) {
            $str .= "value='$value' ";
        }
        if ($checked) {
            $str .= "checked='checked' ";
        }
        $str .= " />";
        return $str;
    }
    public function datetime_widget (FormView $form, $variables=[]) {
        $str = "";
        $vars = array_merge_recursive($form->vars, $variables);
        $widget = isset($vars['widget'])?$vars['widget']:null;

        if ($widget==="single_text") {
            $str .= $this->form_widget_simple($form, $variables);
        } else {
            $str .= "<div";
            $str .= $this->widget_container_attributes($form, $variables);
            $str .= ">";
            $str .= $this->form_errors($form['date']);
            $str .= $this->form_errors($form['time']);
            $str .= $this->form_widget($form['date']);
            $str .= $this->form_widget($form['time']);
            $str .= "</div>";
        }
        return $str;
    }
    public function date_widget (FormView $form, $variables=[]) {
        $str = "";
        $vars = array_merge_recursive($form->vars, $variables);
        $widget = isset($vars['widget'])?$vars['widget']:null;
        $date_pattern = isset($vars['date_pattern'])?$vars['date_pattern']:"";

        if ($widget==="single_text") {
            $str .= $this->form_widget_simple($form, $variables);
        } else {
            $str .= "<div";
            $str .= $this->widget_container_attributes($form, $variables);
            $str .= ">";
            $str .= str_replace([
                "%year%", "%month%", "%day%",
            ],[
                $this->form_widget($form['year'], $variables),
                $this->form_widget($form['month'], $variables),
                $this->form_widget($form['day'], $variables)
            ], $date_pattern);
            $str .= $this->form_errors($form['date']);
            $str .= $this->form_errors($form['time']);
            $str .= $this->form_widget($form['date']);
            $str .= $this->form_widget($form['time']);
            $str .= "</div>";
        }
        return $str;
    }
    public function time_widget (FormView $form, $variables=[]) {
        $str = "";
        $vars = array_merge_recursive($form->vars, $variables);
        $widget = isset($vars['widget'])?$vars['widget']:null;
        $with_minutes = isset($vars['with_minutes'])?$vars['with_minutes']:false;
        $with_seconds = isset($vars['with_seconds'])?$vars['with_seconds']:false;

        if ($widget==="single_text") {
            $str .= $this->form_widget_simple($form, $variables);
        } else {
            $str .= "<div";
            $str .= $this->widget_container_attributes($form, $variables);
            $str .= ">";
            $str .= $this->form_widget($form['hour'], $variables);
            if ($with_minutes) {
                $str .= $this->form_widget($form['minute'], $variables);
            }
            if ($with_seconds) {
                $str .= $this->form_widget($form['second'], $variables);
            }
            $str .= "</div>";
        }
        return $str;
    }
    public function number_widget (FormView $form, $variables=[]) {
        $str = "";
        $vars = array_merge_recursive($form->vars, $variables);
        // {# type="number" doesn't work with floats #}
        $vars['type'] = isset($vars['type']) ? $vars['type'] : "text";
        $str .= $this->form_widget_simple($form, $variables);
        return $str;
    }
    public function integer_widget (FormView $form, $variables=[]) {
        $str = "";
        $vars = array_merge_recursive($form->vars, $variables);
        $vars['type'] = isset($vars['type']) ? $vars['type'] : "number";
        $str .= $this->form_widget_simple($form, $variables);
        return $str;
    }
    public function money_widget (FormView $form, $variables=[]) {
        $str = "";
        $vars = array_merge_recursive($form->vars, $variables);
        $money_pattern = isset($vars['money_pattern']) ? $vars['money_pattern'] : "%widget%";
        $str .= str_replace(
            "%widget%", $this->form_widget_simple($form, $variables), $money_pattern
        );
        return $str;
    }
    public function url_widget (FormView $form, $variables=[]) {
        $str = "";
        $vars = array_merge_recursive($form->vars, $variables);
        $vars['type'] = isset($vars['type']) ? $vars['type'] : "url";
        $str .= $this->form_widget_simple($form, $variables);
        return $str;
    }
    public function search_widget (FormView $form, $variables=[]) {
        $str = "";
        $vars = array_merge_recursive($form->vars, $variables);
        $vars['type'] = isset($vars['type']) ? $vars['type'] : "search";
        $str .= $this->form_widget_simple($form, $variables);
        return $str;
    }
    public function percent_widget (FormView $form, $variables=[]) {
        $str = "";
        $vars = array_merge_recursive($form->vars, $variables);
        $vars['type'] = isset($vars['type']) ? $vars['type'] : "text";
        $str .= $this->form_widget_simple($form, $variables);
        return $str;
    }
    public function password_widget (FormView $form, $variables=[]) {
        $str = "";
        $vars = array_merge_recursive($form->vars, $variables);
        $vars['type'] = isset($vars['type']) ? $vars['type'] : "password";
        $str .= $this->form_widget_simple($form, $variables);
        return $str;
    }
    public function hidden_widget (FormView $form, $variables=[]) {
        $str = "";
        $vars = array_merge_recursive($form->vars, $variables);
        $vars['type'] = isset($vars['type']) ? $vars['type'] : "hidden";
        $str .= $this->form_widget_simple($form, $variables);
        return $str;
    }
    public function email_widget (FormView $form, $variables=[]) {
        $str = "";
        $vars = array_merge_recursive($form->vars, $variables);
        $vars['type'] = isset($vars['type']) ? $vars['type'] : "email";
        $str .= $this->form_widget_simple($form, $variables);
        return $str;
    }
    public function button_widget (FormView $form, $variables=[]) {
        $str = "";
        $vars = array_merge_recursive($form->vars, $variables);
        $label = $vars['label'];
        $label_format = $vars['label_format'];
        $name = $vars['name'];
        $id = $vars['id'];
        $translation_domain = $vars['translation_domain'];

        if (!$label) {
            if ($label_format) {
                $label = $this->commons->format($label_format, [
                    '%name%'=>$name,
                    '%id%'  =>$id,
                ]);
            } else {
                // @todo check with twig original
                $str .= $this->commons->humanize($name);
            }
        }

        $str .= "<button type='";
        $str .= "'";
        $str .= $this->widget_attributes($form, $variables);
        $str .= ">";
        $str .= $this->commons->trans($label, $translation_domain);
        $str .= $label;
        $str .= "</button>";
        return $str;
    }
    public function submit_widget (FormView $form, $variables=[]) {
        $str = "";
        $vars = array_merge_recursive($form->vars, $variables);
        $vars['type'] = isset($vars['type']) ? $vars['type'] : "submit";
        $str .= $this->button_widget($form, $variables);
        return $str;
    }
    public function reset_widget (FormView $form, $variables=[]) {
        $str = "";
        $vars = array_merge_recursive($form->vars, $variables);
        $vars['type'] = isset($vars['type']) ? $vars['type'] : "reset";
        $str .= $this->button_widget($form, $variables);
        return $str;
    }


    public function csrf_token() {
        // @todo must return the token value.
    }

}
