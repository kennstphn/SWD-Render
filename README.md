# SWD-Render


##Scope

The scope of this project is to create a templating solution that conforms to the following standards

1. Usage must be simple!
 1. The implementation must not require extra libraries, rely on other languages, etc
 2. Calling the method must not require lots of variables, or arrays of configs
 3. Recursion and looping must be supported in a way that doesn't depend on hacking existing classes for objects
 4. Syntax for HTML markup must not depend on creating a new language! (gently nudges Twig...)
2. We must have access to logic, conditional statements, etc - AND we have to do this without creating a new templating language (in conflict with 1.iv)
3. Code must not conflict with php frameworks (i.e. wordpress/joomla/concrete5/etc)
 1. Code must not rely on on PHP CONSTANTS for configuration
 2. All functions and logic must be contained within a namespaced class as methods
 3. ~~Code must not rely on global variables~~ 




##Implementation

The solution incorporates my favorite aspect of twig, placeholders for values in html, with OOP while retaining the logical power of PHP that you are familiar with. 

1. include the file Render.php in your code if you haven't done so...
2. choose a template method!
 1. Pass a template(s) (single = string, all = array, controller = object)
 2. Pass "null" for the $template variable, and pre-define (or set default, overridden by option 1) a template by giving your object a property "template" = string
2. render the object via the command \SWD\Render::render($yourObject, $allTemplates);

###Templates: 

When `\SWD\Render::render()` is passed an object and template variable, it looks for a template string in the following places, and stops looking further when it finds one.

1. The `$allTemplates` passed variable (assuming you didn't pass null, and the rendered object has a class name)
  1. If the $allTemplates variable is an object, it looks for the template string in these places (stops when it finds one)
   1. The `$allTemplates->Your\Name\Spaced\ClassName` property which is a string
   2. The result of $allTemplates->__get($YourObjectsClassName)
   3. The result of $allTemplates->get_template_for_Your_Name_Spaced_ClassName($YourObject) (this is for people who don't like magic methods...
  2. If the $allTemplates variable is an array, it looks at $allTemplates['Your\Name\Spaced\ClassName'];
  3. if the $allTemplates variable is a string, we use that. 
  4. booleans, integers, etc are ignored.
3. The object itself for a 'template' property. i.e. `$yourObject->template`
4. If no template is found, a User Notice level error is thrown to the `\SWD\Render::render()`'s caller, and code continues running.

BEGINNERS -- Beginners are suggested to use 1.ii and 1.iii until you understand controllers and want to get into that logic
MVC Programmers -- You can see how this allows clear access to a MVC model using the  `\SWD\Render::render($application,  $yourController)` method. 


####Placeholders

Templates consist of normal html strings with placeholders. The only syntax to know is that placeholders can only consist of the aphabet, numbers, or underscores. This follows the patterns for php properties/variables and methods/functions. For example - `{{twitter_section}}` or `{{my2ndObject}}`.

####Template defining

Templates can be defined (as a default) within a class itself by assigning a string to the 'template' property. Whether a default template is assigned or not, we always look for an override within the global $swdTemplates object. See further details on the options for this under the "Objects" section

>NOTE: The global $swdTemplates can have objects assigned to the template areas. If \SWD\Render::render() finds an object at $swdTemplate->MyClass, it calls the `get_template($object);` method of $swdTemplates->MyClass (not `MyClass->get_template($object);`). This allows for easy extension into conditional templating, but keeps the logic in php where it belongs!



###Objects
>From Template section...

>When `\SWD\Render::render()` is passed an object, it looks for a template string in the following places, and stops looking further when it finds one.

>1. The global object $swdTemplates for a property named 'Namespace_Slashes_Use_Underscores_MyClassName' that is a string
>2. The global object $swdTemplates for a method named 'Namespace_Slashes_Use_Underscores_MyClassName' that returns a string
>3. The object itself for a 'template' property. 
>4. If no template is found, a User Notice level error is thrown to the `\SWD\Render::render()`'s caller.

Once the template string has been defined, `\SWD\Render::render()` grabs all {{placeholder}} patterns within the template string, and replaces it with the first option from the following list that applies.

1. A property with that pattern (minus the brackets) 
2. A method from that object with the pattern as it's name. 

Rendering Recursion then kicks in, and `\SWD\Render::render()` is then called again and passed the evaluated property or method. Nested Objects, Arrays, and strings are thus rendered as long as (they are called by the parent object's template) and (objects have a template).

> NOTE: You can call `\SWD\Render::render($object);` on objects that are from other developers. Since templates assigned via the global $swdTemplates override (not replace) the $object->template property, no class/object hacking is necessary to non-destructively access the public properties/methods of the object;



###Arrays

As just mentioned, \SWD\Render handles arrays as a "foreach" loop, rendering each value. If you want to conditionalize this loop, then you want logic. You'll want to check out the Methods section below


###Strings

if `\SWD\Render::render();` is called on a string, it returns the string as is. This allows for us to call `\SWD\Render::render($arraysOfObjectsAndStrings);` and receive output that is handled appropriately. 

###Methods

This section allows templating logic that does not conflict with our design scope! If you need the result of a function in your template, simply call that function from within the template string as {{your_method_name}}. This is replaced by the result of your function, whether it be a string, another Object (define a template in your $allTemplates variable!) or even an arrayOfStringsAndOrObjects. This becomes a highly powerful tool that enables sophisticated templating, while never once allowing markup language to become logical language. 
