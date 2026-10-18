<?php

test('landing page renders successfully with all required sections and content', function () {
    $response = $this->get('/');

    $response->assertOk()
        // Hero
        ->assertSeeText('iCanSee')
        ->assertSeeText('See Beyond Sight,')
        ->assertSeeText('Empower Independence.')
        ->assertSeeText('An AI-powered visual assistant designed for people with visual impairments.')
        ->assertSeeText('Get Started')
        ->assertSeeText('Support Our Mission')
        // About
        ->assertSeeText('About iCanSee')
        ->assertSeeText('Making Visual Information Accessible to Everyone')
        ->assertSeeText('Our mission is simple:')
        ->assertSeeText('Make visual information accessible to everyone, regardless of their ability to see.')
        // Why iCanSee
        ->assertSeeText('Why iCanSee?')
        ->assertSeeText('Technology That Understands Your Needs')
        ->assertSeeText('One-Tap Simplicity')
        ->assertSeeText('AI-Powered Visual Assistance')
        ->assertSeeText('Voice-First Experience')
        ->assertSeeText('Accessible Anywhere')
        ->assertSeeText('Greater Everyday Independence')
        // How It Works
        ->assertSeeText('How It Works')
        ->assertSeeText('Simple Interactions. Powerful Assistance.')
        ->assertSeeText('One Tap to Ask. Double Tap to See.')
        ->assertSeeText('1. Single Tap — Ask Anything')
        ->assertSeeText('2. Double Tap — Explore Your Surroundings')
        // Features
        ->assertSeeText('Features')
        ->assertSeeText('Everything You Need, Made Simple')
        ->assertSeeText('AI Visual Recognition')
        ->assertSeeText('Voice Questions & Answers')
        ->assertSeeText('Smart Camera Assistance')
        ->assertSeeText('Text Recognition')
        ->assertSeeText('Audio Descriptions')
        ->assertSeeText('Accessible Web Experience')
        ->assertSeeText('Multilingual Support')
        // Who Is iCanSee For
        ->assertSeeText('Who Is iCanSee For?')
        ->assertSeeText('Designed for Accessibility. Built for Independence.')
        ->assertSeeText('People with Visual Impairments')
        ->assertSeeText('Parents & Families')
        ->assertSeeText('Foundations & Nonprofits')
        ->assertSeeText('Accessibility Advocates')
        // Getting Started
        ->assertSeeText('Getting Started')
        ->assertSeeText('Your Journey with iCanSee')
        ->assertSeeText('Step 1')
        ->assertSeeText('Registration')
        ->assertSeeText('Step 2')
        ->assertSeeText('Account Activation')
        ->assertSeeText('Step 3')
        ->assertSeeText('Open iCanSee')
        ->assertSeeText('Step 4')
        ->assertSeeText('Start Exploring')
        ->assertSeeText('Register a User')
        // Support Our Mission
        ->assertSeeText('Help Us Make Accessibility Possible')
        ->assertSeeText('Make a Donation')
        ->assertSeeText('Become a Partner')
        // For Foundations & Communities
        ->assertSeeText('For Foundations & Communities')
        ->assertSeeText('Empower Your Community with iCanSee')
        ->assertSeeText('iCanSee Management Portal')
        ->assertSeeText('Partner With Us')
        // Our Vision
        ->assertSeeText('Our Vision')
        ->assertSeeText('A World Where Visual Information Has No Barriers')
        ->assertSeeText('Technology for Accessibility. Innovation for Independence.')
        // FAQ
        ->assertSeeText('Frequently Asked Questions')
        ->assertSeeText('What is iCanSee?')
        ->assertSeeText('Do I need to install an application?')
        ->assertSeeText('How do I use iCanSee?')
        ->assertSeeText('Is iCanSee free?')
        ->assertSeeText('How can I register someone?')
        ->assertSeeText('Can I support iCanSee through donations?')
        ->assertSeeText('Can iCanSee replace a mobility aid?')
        // Final CTA & Footer
        ->assertSeeText("Let's Make the World More Accessible, Together.")
        ->assertSeeText('© 2026 iCanSee. All rights reserved.');
});

test('landing page v2 renders successfully with dark luminous glassmorphic design and required content', function () {
    $response = $this->get('/v2');

    $response->assertOk()
        // Hero
        ->assertSeeText('iCanSee')
        ->assertSeeText('See Beyond Sight,')
        ->assertSeeText('Empower Independence.')
        ->assertSeeText('An AI-powered visual assistant designed for people with visual impairments.')
        ->assertSeeText('Get Started')
        ->assertSeeText('Support Our Mission')
        // About
        ->assertSeeText('About iCanSee')
        ->assertSeeText('Making Visual Information Accessible to Everyone')
        ->assertSeeText('Our mission is simple:')
        ->assertSeeText('Make visual information accessible to everyone, regardless of their ability to see.')
        // Why iCanSee
        ->assertSeeText('Why iCanSee?')
        ->assertSeeText('Technology That Understands Your Needs')
        ->assertSeeText('One-Tap Simplicity')
        ->assertSeeText('AI-Powered Visual Assistance')
        ->assertSeeText('Voice-First Experience')
        ->assertSeeText('Accessible Anywhere')
        ->assertSeeText('Greater Everyday Independence')
        // How It Works
        ->assertSeeText('How It Works')
        ->assertSeeText('Simple Interactions. Powerful Assistance.')
        ->assertSeeText('One Tap to Ask. Double Tap to See.')
        ->assertSeeText('1. Single Tap — Ask Anything')
        ->assertSeeText('2. Double Tap — Explore Your Surroundings')
        // Features
        ->assertSeeText('Features')
        ->assertSeeText('Everything You Need, Made Simple')
        ->assertSeeText('AI Visual Recognition')
        ->assertSeeText('Voice Questions & Answers')
        ->assertSeeText('Smart Camera Assistance')
        ->assertSeeText('Text Recognition')
        ->assertSeeText('Audio Descriptions')
        ->assertSeeText('Accessible Web Experience')
        ->assertSeeText('Multilingual Support')
        // Who Is iCanSee For
        ->assertSeeText('Who Is iCanSee For?')
        ->assertSeeText('Designed for Accessibility. Built for Independence.')
        ->assertSeeText('People with Visual Impairments')
        ->assertSeeText('Parents & Families')
        ->assertSeeText('Foundations & Nonprofits')
        ->assertSeeText('Accessibility Advocates')
        // Getting Started
        ->assertSeeText('Getting Started')
        ->assertSeeText('Your Journey with iCanSee')
        ->assertSeeText('Step 1')
        ->assertSeeText('Registration')
        ->assertSeeText('Step 2')
        ->assertSeeText('Account Activation')
        ->assertSeeText('Step 3')
        ->assertSeeText('Open iCanSee')
        ->assertSeeText('Step 4')
        ->assertSeeText('Start Exploring')
        ->assertSeeText('Register a User')
        // Support Our Mission
        ->assertSeeText('Help Us Make Accessibility Possible')
        ->assertSeeText('Make a Donation')
        ->assertSeeText('Become a Partner')
        // For Foundations & Communities
        ->assertSeeText('For Foundations & Communities')
        ->assertSeeText('Empower Your Community with iCanSee')
        ->assertSeeText('iCanSee Management Portal')
        ->assertSeeText('Partner With Us')
        // Our Vision
        ->assertSeeText('Our Vision')
        ->assertSeeText('A World Where Visual Information Has No Barriers')
        ->assertSeeText('Technology for Accessibility. Innovation for Independence.')
        // FAQ
        ->assertSeeText('Frequently Asked Questions')
        ->assertSeeText('What is iCanSee?')
        ->assertSeeText('Do I need to install an application?')
        ->assertSeeText('How do I use iCanSee?')
        ->assertSeeText('Is iCanSee free?')
        ->assertSeeText('How can I register someone?')
        ->assertSeeText('Can I support iCanSee through donations?')
        ->assertSeeText('Can iCanSee replace a mobility aid?')
        // Final CTA & Footer
        ->assertSeeText("Let's Make the World More Accessible, Together.")
        ->assertSeeText('© 2026 iCanSee. All rights reserved.');
});

