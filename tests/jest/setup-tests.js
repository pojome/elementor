import '@testing-library/jest-dom/extend-expect';

// React dependencies.
// when the required method called with 'react' or 'react-dom' as an argument, the moduleNameMapper in jest config
// creates a reference to the qunit vendors files.
global.PropTypes = require( 'prop-types' );
global.React = require( 'react' );
global.ReactDOM = require( 'react-dom' );

// Mock to wordpress translation function.
global.__ = ( text ) => text;
