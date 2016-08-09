# SWD-Render


##Scope

The scope of this project is to create a templating solution that conforms to the following standards

1. Usage must be simple!
 1. The implementation must not require extra libraries, rely on other languages, etc
 2. Calling the method must not require lots of variables, or arrays of configs
 3. Recursion and looping must be supported in a way that doesn't depend on hacking existing classes for objects
 4. Syntax for HTML markup must not depend on creating a new language! (gently nudges Twig...)
2. Code must not conflict with php frameworks (i.e. wordpress/joomla/concrete5/etc)
 1. Code must not rely on on PHP CONSTANTS for configuration
 2. All functions and logic must be contained within a namespaced class as methods
 3. ~~Code must not rely on global variables~~ 

>####SCOPE ADJUSTMENT on point 2.iii
It became apparent that implementing Section 1 (usage must be simple) did not allow for both 2.i and 2.iii. Since 2.1 was a bigger area of concern for potential conflicts, I made the design decision to eliminate 2.3, and rely on a single global $swdTemplates for templating options. This also enabled much cleaner logic chain and recursion of non-hacked objects



##Implementation

The solution incorporates my favorite aspect of twig, placeholders for values in html, with OOP while retaining the logical power of PHP that you are familiar with. 

1. include the file Render.php in your code if you haven't done so...
2. choose a template method!
 1. set a template string within the global object $swdTemplates for your object, using {{property}} for each $object->property that you wish to display -- or even {{my_method}} for $object->my_method()
 2. pre-define (or set default, overridden by option 1) a template by giving your object a property "template" = string
2. render the object via the command \SWD\Render::render($yourObject);

###Templates: 

When `\SWD\Render::render()` is passed an object, it looks for a template string in the following places, and stops looking further when it finds one.

1. The global object $swdTemplates for a property named 'Namespace_Slashes_Use_Underscores_MyClassName' that is a string
2. The global object $swdTemplates for a method named 'Namespace_Slashes_Use_Underscores_MyClassName' that returns a string
3. The object itself for a 'template' property. 
4. If no template is found, a User Notice level error is thrown to the `\SWD\Render::render()`'s caller.

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

As just mentioned, \SWD\Render handles arrays as a "foreach" loop, rendering each value. If you want to conditionalize this loop, then you want logic. If you want logic, then you should do one of the following

1. look to php as your solution (i.e. create a class with method `public function method_that_returns_filtered_array(){};` for your object, and use {{method_that_returns_filtered_array}} in your template string)
2. look to javascript as your solution 
3. Use a templating engine that includes logic



###Strings

if `\SWD\Render::render();` is called on a string, it returns the string as is. (There is no option to return vs. echo in this case)
This allows for us to call `\SWD\Render::render($arrayOfObjectsAndStrings);` and receive output that is handled appropriately. 

