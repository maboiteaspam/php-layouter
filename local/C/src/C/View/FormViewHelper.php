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
    public function form(FormView $form, $variables=[]) {
        $str = '';
        $str .= $this->form_start($form);
        $str .= $this->form_rows($form, $variables);
        $str .= $this->form_end($form);
        return $str;
    }
    public function form_start (FormView $form, $variables=[]) {

        $vars = array_merge([
            'method'=>'get',
            'name'=>'',
            'action'=>'',
            'attr'=>[],
            'value'=>[],
        ], $form->vars, $variables);

        $method = $this->commons->upper($vars['method']);
        $name = ($vars['name']);
        $action = ($vars['action']);
        $form_method = '';

        if (in_array($method, ['GET', 'POST',])) {
            $form_method = $method;
        } else {
            $form_method = 'POST';
        }

        $str = '';
        $str .= "<form name='$name' method='".$this->commons->lower($form_method)."' action='$action' ";
        foreach ($vars['attr'] as $attr=>$val) {
            $str .= " $attr='$val' ";
        }
        $str .= $this->form_enctype($form, $vars);
        $str .= " >";
        if ($form_method!==$method) {
            $str .= "<input type='hidden' name='_method' value='$method' />";
        }
        return $str;
    }
    public function form_widget (FormView $form, $variables=[]) {
        $str = '';
        $vars = array_merge([
            'compound' => false,
            'type' => 'text',
        ], $form->vars, $variables);

        if ($vars['compound']) {
            $str .= $this->form_widget_compound($form, $vars);
        } else {
            if ($vars['type']==='choice')
                $str .= $this->choice_widget($form, $vars);
            else
                $str .= $this->form_widget_simple($form, $vars);
        }
        return $str;
    }
    public function form_widget_compound (FormView $form, $variables=[]) {
        $str = '';
        $str .= "<div ";
        $str .= $this->widget_container_attributes($form, $variables);
        if (!$form->parent) {
            $str .= $this->form_errors($form);
        }
        $str .= ">";
        $str .= $this->form_rows($form, $variables);
        $str .= "</div>";
        $form->setRendered();
        return $str;
    }
    public function form_widget_simple (FormView $form, $variables=[]) {
        $str = '';
        $vars = array_merge([
            'type'=>'text',
            'value'=>null,
        ], $form->vars, $variables);

        $type = $vars['type'];
        $val = $vars['value'];
        $str .= "<input ";
        $str .= "type='$type' ";
        $str .= $this->widget_attributes($form, $vars);
        if ($val) {
            $str .= "value='$val' ";
        }
        $str .= "/>";
        $form->setRendered();
        return $str;
    }
    public function widget_attributes (FormView $form, $variables=[]) {
        $str = '';
        $vars = array_merge([
            'id'=>null,
            'full_name'=>null,
            'read_only'=>false,
            'required'=>false,
            'disabled'=>false,
            'attr'=>[],
        ], $form->vars, $variables);

        $id = ($vars['id']);
        $full_name = ($vars['full_name']);

        $str .= "id='$id' ";
        $str .= "name='$full_name' ";
        if ($vars['read_only'] && !array_key_exists('readonly', $vars['attr'])) $str .= "readonly='readonly'";
        if ($vars['disabled']) $str .= "disabled='disabled'";
        if ($vars['required']) $str .= "required='required'";

        $str .= $this->block_attributes ($vars['attr']);
        return $str;
    }
    public function block_attributes ($attrs) {
        $str = '';
        foreach ($attrs as $attr=>$val) {
            if (in_array($attr, ['placeholder', 'title',])) {
                $str .= " $attr='$val' "; // here should do translation.
            } else if ($val===true) { // triple eq ? or double eq ?
                $str .= " $attr='$attr' ";
            } else if ($val!==false) { // triple eq ? or double eq ?
                $str .= " $attr='$val' ";
            }
        }
        return $str;
    }
    public function button_attributes (FormView $form, $variables=[]) {
        $str = '';
        $vars = array_merge([
            'id'=>null,
            'full_name'=>null,
            'read_only'=>false,
            'required'=>false,
            'disabled'=>false,
            'attr'=>[],
        ], $form->vars, $variables);

        $id = ($vars['id']);
        $full_name = ($vars['full_name']);

        $str .= "id='$id' ";
        $str .= "name='$full_name' ";
        if ($vars['read_only'] && !array_key_exists('readonly', $vars['attr'])) $str .= "readonly='readonly'";
        if ($vars['disabled']) $str .= "disabled='disabled'";
        if ($vars['required']) $str .= "required='required'";

        foreach ($vars['attr'] as $attr=>$val) {
            if (in_array($attr, ['placeholder', 'title',])) {
                $str .= " $attr='$val' "; // here should do translation.
            } else if ($val==true) { // triple eq ? or double eq ?
                $str .= " $attr='$attr' ";
            } else if ($val!=false) { // triple eq ? or double eq ?
                $str .= " $attr='$val' ";
            }
        }
        $form->setRendered();
        return $str;
    }
    public function widget_container_attributes (FormView $form, $variables=[]) {
        $str = '';
        $vars = array_merge([
            'id'=>null,
            'attr'=>[],
        ], $form->vars, $variables);

        if ($vars['id']) {
            $str .= "id='".$vars['id']."'";
        }
        foreach ($vars['attr'] as $attr=>$val) {
            if (in_array($attr, ['placeholder', 'title',])) {
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
        $str = '';
        $vars = array_merge([
            'render_rest'=>true,
        ], $form->vars, $variables);
        if ($vars['render_rest']) {
            $str .= $this->form_rest($form, $variables);
        }
        $str .= "</form>";
        $form->setRendered();
        return $str;
    }

    public function form_enctype (FormView $form, $variables=[]) {
        $str = '';
        $vars = array_merge($form->vars, [
            'multipart'=>false,
        ], $variables);
        if ($vars['multipart']) {
            $str .= " enctype='multipart/form-data' ";
        }
        return $str;
    }

    public function form_errors (FormView $form, $variables=[]) {
        $str = '';
        $vars = array_merge([
            'errors'=>[],
        ], $form->vars, $variables);
        if (count($vars['errors'])) {
            $str .= "<ul>";
            foreach ($vars['errors'] as $error) {
                $str .= "<li>";
                $str .= $error->getMessage();
                $str .= "</ul>";
            }
            $str .= "</ul>";
        }
        return $str;
    }

    public function form_label (FormView $form, $label=null, $variables=[]) {
        $str = '';
        $vars = array_merge([
            'label'=>$label,
            'compound'=>false,
            'label_attr'=>[],
            'required'=>false,
            'name'=>null,
            'label_format'=>null,
            'translation_domain'=>null,
        ], $form->vars, $variables);
        $id = $vars['id'];
        $label = $vars['label'];
        $compound = $vars['compound'];
        $label_attr = $vars['label_attr'];
        $required = $vars['required'];
        $name = $vars['name'];
        $label_format = $vars['label_format'];
        $translation_domain = $vars['translation_domain'];

        $label = $label ? $label : $name;
        $label = $label ? $label : $id;

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
                    $label = $this->commons->humanize($name);
                }
            }

            $str .= "<label ";
            foreach ($label_attr as $name=>$val) {
                $str .= "$name='$val' ";
            }
            $str .= ">";
            $str .= $this->commons->trans($label, [], $translation_domain) ;// @todo check with original twig.
            $str .= "</label>";
        }
        return $str;
    }
    public function form_row (FormView $form, $variables=[]) {
        $str = '';
        $str .= "<div>";
        $str .= $this->form_label($form, null, $variables);
        $str .= $this->form_errors($form, $variables);
        $str .= $this->form_widget($form, $variables);
        $str .= "</div>";
        $form->setRendered();
        return $str;
    }
    public function form_rows (FormView $form, $variables=[]) {
        $str = '';
        foreach ($form->children as $child) {
            $str .= $this->form_row($child, $variables);
        }
        return $str;
    }
    public function form_rest (FormView $form, $variables=[]) {
        $str = '';
        foreach ($form->children as $child) {
            if (!$child->isRendered()) {
                if ($child->vars['name']==='_token')
                    $str .= $this->hidden_widget($child, $variables);
                else $str .= $this->form_row($child, $variables);
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
        $str = '';
        $str .= $this->form_widget ($form, $variables);
        return $str;
    }
    public function textarea_widget (FormView $form, $variables=[]) {
        $str = '';
        $vars = array_merge([
            'value'=>null,
        ], $form->vars, $variables);
        $str .= "<textarea ";
        $str .= $this->widget_attributes($form, $vars);
        $str .= ">";
        $str .= $vars['value'];
        $str .= "</textarea>";
        return $str;
    }
    public function choice_widget (FormView $form, $variables=[]) {
        $str = '';
        $vars = array_merge([
            'expanded'=>false,
        ], $form->vars, $variables);
        if ($vars['expanded']) {
            $str .= $this->choice_widget_expanded ($form, $vars);
        } else {
            $str .= $this->choice_widget_collapsed ($form, $vars);
        }
        return $str;
    }
    public function choice_widget_expanded (FormView $form, $variables=[]) {
        $str = '';
        $str .= "<div ".$this->widget_container_attributes($form, $variables).">";
        $str .= $this->form_widget_simple($form, $variables);
        $str .= $this->form_label($form, null, $variables);
        $str .= "</div>";
        return $str;
    }
    public function choice_widget_collapsed (FormView $form, $variables=[]) {
        $str = '';
        $vars = array_merge([
            'value'=>null,
            'required'=>null,
            'placeholder'=>false,
            'placeholder_in_choices'=>false,
            'multiple'=>false,
            'preferred_choices'=>[],
            'choices'=>[],
            'separator'=>'',
            'choice_translation_domain'=>null,
        ], $form->vars, $variables);

        $value = $vars['value'];
        $required = $vars['required'];
        $placeholder = $vars['placeholder'];
        $placeholder_in_choices = $vars['placeholder_in_choices'];
        $preferred_choices = $vars['preferred_choices'];
        $choices = $vars['choices'];
        $separator = $vars['separator'];
        $multiple = $vars['multiple'];

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
            $vars['options'] = $preferred_choices;
            $this->choice_widget_options($form, $vars);
            if (count($choices) && $separator) {
                $str .= "<option disabled='disabled'>$separator</option>";

            }
        }
        $vars['options'] = $choices;
        $str .= $this->choice_widget_options ($form, $vars);
        $str .= "</select>";

        return $str;
    }
    public function choice_widget_options (FormView $form, $variables=[]) {
        $str = '';
        $vars = array_merge([
            'options'=>[],
            'choice_translation_domain'=>null,
        ], $form->vars, $variables);

        foreach ($vars['options'] as $group_label => $choice) {
            if ($choice instanceof \Traversable || is_array($choice)) {
                $str .= "<optgroup label='";
                $str .= $this->commons->trans($group_label, [], $vars['choice_translation_domain']);
                $str .= "' ";
                $str .= $this->choice_widget_options($form, ['options'=>$choice]);
                $str .= "</optgroup>";
            } else {
                $choiceLabel = $choice->label;
                $choiceValue = $choice->value;
                $selected_value = isset($choice->selected_value) ? $choice->selected_value : [];
                $str .= "<option value='$choiceValue' ";
                $str .= $this->block_attributes($choice->attr); // @todo check it, coz original code called block('attributes')
                if ($choiceValue===$selected_value || in_array($choiceValue, $selected_value)) {
                    $str .= "selected='selected' ";
                }
                $str .= ">";
                $str .= $this->commons->trans($choiceLabel, [], $vars['choice_translation_domain']);
                $str .= "</option>";
            }
        }
        return $str;
    }
    public function checkbox_widget (FormView $form, $variables=[]) {
        $str = '';
        $vars = array_merge([
            'value'=>null,
            'checked'=>null,
        ], $form->vars, $variables);

        $str .= "<input type='checkbox' ";
        $str .= $this->widget_attributes ($form, $vars);
        if ($vars['value']) {
            $str .= "value='".$vars['value']."' ";
        }
        if ($vars['checked']) {
            $str .= "checked='checked' ";
        }
        $str .= " />";
        return $str;
    }
    public function radio_widget (FormView $form, $variables=[]) {
        $str = '';
        $vars = array_merge([
            'value'=>null,
            'checked'=>null,
        ], $form->vars, $variables);

        $str .= "<input type='radio' ";
        $str .= $this->widget_attributes ($form, $vars);
        if ($vars['value']) {
            $str .= "value='".$vars['value']."' ";
        }
        if ($vars['checked']) {
            $str .= "checked='checked' ";
        }
        $str .= " />";
        return $str;
    }
    public function datetime_widget (FormView $form, $variables=[]) {
        $str = '';
        $vars = array_merge([
            'widget'=>null,
        ], $form->vars, $variables);

        if ($vars['widget']==="single_text") {
            $str .= $this->form_widget_simple($form, $vars);
        } else {
            $str .= "<div ";
            $str .= $this->widget_container_attributes($form, $vars);
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
        $str = '';
        $vars = array_merge([
            'widget'=>null,
            'date_pattern'=>null,
        ], $form->vars, $variables);

        if ($vars['widget']==="single_text") {
            $str .= $this->form_widget_simple($form, $vars);
        } else {
            $str .= "<div ";
            $str .= $this->widget_container_attributes($form, $vars);
            $str .= ">";
            $str .= str_replace([
                "%year%", "%month%", "%day%",
            ],[
                $this->form_widget($form['year'], $vars),
                $this->form_widget($form['month'], $vars),
                $this->form_widget($form['day'], $vars)
            ], $vars['date_pattern']);
            $str .= $this->form_errors($form['date']);
            $str .= $this->form_errors($form['time']);
            $str .= $this->form_widget($form['date']);
            $str .= $this->form_widget($form['time']);
            $str .= "</div>";
        }
        return $str;
    }
    public function time_widget (FormView $form, $variables=[]) {
        $str = '';
        $vars = array_merge([
            'widget'=>null,
            'with_minutes'=>null,
            'with_seconds'=>null,
        ], $form->vars, $variables);
        $widget = $vars['widget'];
        $with_minutes = $vars['with_minutes'];
        $with_seconds = $vars['with_seconds'];

        if ($widget==="single_text") {
            $str .= $this->form_widget_simple($form, $vars);
        } else {
            $str .= "<div ";
            $str .= $this->widget_container_attributes($form, $vars);
            $str .= ">";
            $str .= $this->form_widget($form['hour'], $vars);
            if ($with_minutes) {
                $str .= $this->form_widget($form['minute'], $vars);
            }
            if ($with_seconds) {
                $str .= $this->form_widget($form['second'], $vars);
            }
            $str .= "</div>";
        }
        return $str;
    }
    public function number_widget (FormView $form, $variables=[]) {
        $str = '';
        $vars = array_merge($form->vars, $variables, [
            'type'=>'text',
        ]);
        // {# type="number" doesn't work with floats #}
        $str .= $this->form_widget_simple($form, $vars);
        return $str;
    }
    public function integer_widget (FormView $form, $variables=[]) {
        $str = '';
        $vars = array_merge($form->vars, $variables, [
            'type'=>'number',
        ]);
        $str .= $this->form_widget_simple($form, $vars);
        return $str;
    }
    public function money_widget (FormView $form, $variables=[]) {
        $str = '';
        $vars = array_merge([
            'money_pattern'=>'%widget%',
        ], $form->vars, $variables);
        $str .= str_replace(
            "%widget%", $this->form_widget_simple($form, $variables), $vars['money_pattern']
        );
        return $str;
    }
    public function url_widget (FormView $form, $variables=[]) {
        $str = '';
        $vars = array_merge($form->vars, $variables, [
            'type'=>'url',
        ]);
        $str .= $this->form_widget_simple($form, $vars);
        return $str;
    }
    public function search_widget (FormView $form, $variables=[]) {
        $str = '';
        $vars = array_merge($form->vars, $variables, [
            'type'=>'search',
        ]);
        $str .= $this->form_widget_simple($form, $vars);
        return $str;
    }
    public function percent_widget (FormView $form, $variables=[]) {
        $str = '';
        $vars = array_merge($form->vars, $variables, [
            'type'=>'text',
        ]);
        $str .= $this->form_widget_simple($form, $vars);
        return $str;
    }
    public function password_widget (FormView $form, $variables=[]) {
        $str = '';
        $vars = array_merge($form->vars, $variables, ['type'=>'password']);
        $str .= $this->form_widget_simple($form, $vars);
        return $str;
    }
    public function hidden_widget (FormView $form, $variables=[]) {
        $str = '';
        $vars = array_merge($form->vars, $variables, ['type'=>'hidden']);
        $str .= $this->form_widget_simple($form, $vars);
        return $str;
    }
    public function email_widget (FormView $form, $variables=[]) {
        $str = '';
        $vars = array_merge($form->vars, $variables, ['type'=>'email']);
        $str .= $this->form_widget_simple($form, $vars);
        return $str;
    }
    public function text_widget (FormView $form, $variables=[]) {
        $str = '';
        $vars = array_merge($form->vars, $variables, ['type'=>'text']);
        $str .= $this->form_widget_simple($form, $vars);
        return $str;
    }
    public function button_widget (FormView $form, $variables=[]) {
        $str = '';
        $vars = array_merge([
            'translation_domain'=>null,
            'label'=>'',
            'label_format'=>'',
            'name'=>'',
        ], $form->vars, $variables);
        $id = $vars['id'];
        $label = $vars['label'];
        $label_format = $vars['label_format'];
        $name = $vars['name']?$vars['name']:$id;
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
        $str .= $this->button_attributes($form, $vars);
        $str .= ">";
        $str .= $this->commons->trans($label, [], $translation_domain);
        $str .= "</button>";
        $form->setRendered();
        return $str;
    }
    public function submit_widget (FormView $form, $variables=[]) {
        $str = '';
        $vars = array_merge($form->vars, $variables, [
            'type'=>'submit',
        ]);
        $str .= $this->button_widget($form, $vars);
        return $str;
    }
    public function reset_widget (FormView $form, $variables=[]) {
        $str = '';
        $vars = array_merge($form->vars, $variables, [
            'type'=>'reset',
        ]);
        $str .= $this->button_widget($form, $vars);
        return $str;
    }


    public function csrf_token() {
        // @todo must return the token value.
    }

}
