import { __ } from "@wordpress/i18n";
import apiFetch from "@wordpress/api-fetch";
import {
  __experimentalText as Text,
  __experimentalHeading as Heading,
  __experimentalVStack as VStack,
  __experimentalHStack as HStack,
  Button,
  Spinner,
  Snackbar,
} from "@wordpress/components";
import { useState, useEffect } from "@wordpress/element";

import Form from "./Form";

export default function SettingsPage(props) {
  const [settings, setSettings] = useState(null);
  const [isSaving, setIsSaving] = useState(false);
  const [showNotice, setShowNotice] = useState(false);

  // Charger uniquement les settings lorsque le composant est chargé
  useEffect(() => {
    apiFetch({
      path: "/proxilog-features/v1/settings",
      method: "GET",
    })
      .then(response => {
        setSettings(response);
      })
      .catch(error => {
        console.error("Error loading settings:", error);
      });
  }, []);

  // Gestion de l'enregistrement des settings
  const handleSaveSettings = () => {
    setIsSaving(true);

    apiFetch({
      path: "/proxilog-features/v1/settings",
      method: "POST",
      data: settings,
    }).then(response => {
      setShowNotice(true);
      setIsSaving(false);

      setTimeout(() => setShowNotice(false), 3000);
    });
  };

  return (
    <>
      <HStack as="header" spacing={4} className="proxilog-header">
        <VStack spacing={2}>
          <Heading level={1}>Proxilog Features</Heading>
          <Text variant="muted">
            This is the settings page for the Proxilog Features plugin.
          </Text>
        </VStack>
        <Button
          variant="primary"
          onClick={handleSaveSettings}
          isBusy={isSaving}
          disabled={isSaving}
        >
          {isSaving
            ? __("Saving…", "proxilog-features")
            : __("Save Settings", "proxilog-features")}
        </Button>
      </HStack>
      <VStack as="main" spacing={2} className="proxilog-content">
        {settings ? (
          <Form settings={settings} onChange={value => setSettings(value)} />
        ) : (
          <Spinner />
        )}
      </VStack>
      {showNotice && (
        <Snackbar
          className="proxilog-snackbar"
          explicitDismiss={true}
          onRemove={() => setShowNotice(false)}
          type="success"
        >
          {__("Settings saved.", "proxilog-features")}
        </Snackbar>
      )}
    </>
  );
}
