# POSM: PHP-Only Site Management

[Live Demo](http://bloch.ca/posm/?login) (Username: demodemo/Password: demodemo)

POSM is my vision of what content management software should be. Web designers build beautiful templates for
software like WordPress, only to have to customize (and often minimize) portions of the back end, create alternate
stylesheets for content editors, and in many cases, even write a usage guide for helping the non-technical owner
of the website to be able to change anything down the road, when they will surely forget anything you show them.

Everything should be simple, transparent, and straightforward. The following features attempt to apply that:

## You got back end on my front end

The only thing that isn't obvious is how to log in to the website—this is how it should be, so users don't find the
login form. All it means is that the site administrator needs to keep track of three things: the login URL (which is
the installation URL followed by `?login`), their username, and password. Everything else is clear once logged in;
the back end works right on top of the front end. There isn't anything else they have to remember.

Pages can be edited by navigating to them normally and clicking edit in the toolbar. They can also be added, viewed
in their hierarchy on a simple listing page, and be deleted from this page. The ability to move pages around in
the hierarchy is an upcoming feature, but as of now pages cannot be moved from their original location in the hierarchy.

There is also a simple settings page, and a logout button, and that's it. There isn't much else to POSM, and there needn't
be. The back end is as easy to navigate as the website that you design, and the site's owner will have no problem finding
the right page to edit.

## Inline content editing

There should be no Edit page, that pulls you away from the content you are writing and drops you in a textarea that,
no matter how well it attempts to, can never identically parallel what the published content will look like. This
also adds another layer of maintenance, as editor styles can easily become out of sync.

With POSM, the page content itself is made editable using the `contenteditable` HTML5 attribute. Changes made to
the page content are reflected on save, and what you see is EXACTLY what you get. There's also an option to toggle
a code editor, for cases where someone may want to manually tweak the HTML output of the visual editor, or
maybe just paste in code for some embeddable content.

At this point in time the visual editor uses the very simple [hallo editor](http://hallojs.org), which works only fairly
well. I plan to implement a more robust content editing experience that can be easily customized on a per-theme basis
to enable/disable certain editing options that apply to a specific theme, and perhaps to allow for multiple editable
areas in a page, which would let site designers build complicated theme designs that are hard for the client to break
while being easy to customize in places that should be.

## Damn easy templating

Writing templates for most themable websites isn't that hard, but I thought I could make it even easier. Writing a
template for POSM is dead simple, and I plan to write an API reference so that all functions that are available for
calling in a template are well-documented and easy to access. Until then, see an existing template for examples
of how easy it is to create a POSM site.

## No database?

The owner of the website shouldn't have their content tied to a database just for ease of editing. With POSM, the
content is kept in the installation directory, so POSM can be moved around from server to server without needing to
migrate database tables.

It also means that you can edit the files yourself, if you prefer to use a different code editor. Just edit the
file in place as you would edit a static HTML file.
