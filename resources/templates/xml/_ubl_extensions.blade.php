<ext:UBLExtensions>
    <ext:UBLExtension>
        <ext:ExtensionContent>
            <sts:DianExtensions>
                @isset($resolution)
                    @if (preg_replace("/[\r\n|\n|\r]+/", "", $resolution->type_document_id) === '1' || preg_replace("/[\r\n|\n|\r]+/", "", $resolution->type_document_id) === '2' || preg_replace("/[\r\n|\n|\r]+/", "", $resolution->type_document_id) === '3' || preg_replace("/[\r\n|\n|\r]+/", "", $resolution->type_document_id) === '11')
                        @includeWhen($resolution->resolution, 'xml._invoice_control')
                    @endif
                @endisset
                <sts:InvoiceSource>
                    <cbc:IdentificationCode listAgencyID="6" listAgencyName="United Nations Economic Commission for Europe" listSchemeURI="urn:oasis:names:specification:ubl:codelist:gc:CountryIdentificationCode-2.1">{{preg_replace("/[\r\n|\n|\r]+/", "", $company->country->code)}}</cbc:IdentificationCode>
                </sts:InvoiceSource>
                <sts:SoftwareProvider>
                    <sts:ProviderID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)" @if (preg_replace("/[\r\n|\n|\r]+/", "", $company->type_document_identification_id) === '6' || preg_replace("/[\r\n|\n|\r]+/", "", $company->type_document_identification_id) === '3') schemeID="{{preg_replace("/[\r\n|\n|\r]+/", "", $company->dv)}}" @endif @if (preg_replace("/[\r\n|\n|\r]+/", "", $company->type_document_identification_id) === '6' || preg_replace("/[\r\n|\n|\r]+/", "", $company->type_document_identification_id) === '3') schemeName="31" @else schemeName="{{preg_replace("/[\r\n|\n|\r]+/", "", $company->type_document_identification->code)}}" @endif >{{preg_replace("/[\r\n|\n|\r]+/", "", $company->identification_number)}}</sts:ProviderID>
                    @if($typeDocument->code == 94 || $typeDocument->code == 93)
                        <sts:SoftwareID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)">{{preg_replace("/[\r\n|\n|\r]+/", "", $company->software->identifier_eqdocs)}}</sts:SoftwareID>
                    @elseif($typeDocument->code == 05)
                        <sts:SoftwareID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)">{{preg_replace("/[\r\n|\n|\r]+/", "", $company->software->identifier_support_document)}}</sts:SoftwareID>
                    @else
                        <sts:SoftwareID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)">{{preg_replace("/[\r\n|\n|\r]+/", "", $company->software->identifier)}}</sts:SoftwareID>
                    @endif
                </sts:SoftwareProvider>
                <sts:SoftwareSecurityCode schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)"/>
                <sts:AuthorizationProvider>
                    <sts:AuthorizationProviderID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)" schemeID="4" schemeName="31">800197268</sts:AuthorizationProviderID>
                </sts:AuthorizationProvider>
                @if(isset($QRCode))
                    <sts:QRCode>{{$QRCode}}</sts:QRCode>
                @endif
            </sts:DianExtensions>
        </ext:ExtensionContent>
    </ext:UBLExtension>
    <ext:UBLExtension>
        <ext:ExtensionContent/>
    </ext:UBLExtension>
    @if(isset($request['k_supplement']))
        <ext:UBLExtension>
            <ext:ExtensionContent>
                <CustomTagGeneral>
                    <Interoperabilidad>
                        <Group schemeName="Exportación">
                            <Collection schemeName="DATOS ADICIONALES">
                                <AdditionalInformation>
                                    <name>Responsable/Encargado</name>
                                    <value>{{$request['k_supplement']['responsible_incharge']}}</value>
                                </AdditionalInformation>
                                <AdditionalInformation>
                                    <name>Lugar de Salida</name>
                                    <value>{{$request['k_supplement']['departure_place']}}</value>
                                </AdditionalInformation>
                                <AdditionalInformation>
                                    <name>Medio de transporte</name>
                                    <value>{{$request['k_supplement']['conveyance']}}</value>
                                </AdditionalInformation>
                                <AdditionalInformation>
                                    <name>Tipo de Doc.de transporte</name>
                                    <value>{{$request['k_supplement']['transport_document_type']}}</value>
                                </AdditionalInformation>
                                <AdditionalInformation>
                                    <name>N° de Doc. de transporte</name>
                                    <value>{{$request['k_supplement']['transport_document_number']}}</value>
                                </AdditionalInformation>
                                <AdditionalInformation>
                                    <name>Transportadora o Tramitadora</name>
                                    <value>{{$request['k_supplement']['transporter_processor']}}</value>
                                </AdditionalInformation>
                                <AdditionalInformation>
                                    <name>País de Origen de la M/cia</name>
                                    <value>{{$request['k_supplement']['merchandise_origin_country']}}</value>
                                </AdditionalInformation>
                                <AdditionalInformation>
                                    <name>Destino</name>
                                    <value>{{$request['k_supplement']['destination']}}</value>
                                </AdditionalInformation>
                                <AdditionalInformation>
                                    <name>Términos de pago</name>
                                    <value>{{$request['k_supplement']['payment_means']}}</value>
                                </AdditionalInformation>
                                <AdditionalInformation>
                                    <name>Seguro</name>
                                    <value>{{$request['k_supplement']['insurance_carrier']}}</value>
                                </AdditionalInformation>
                                <AdditionalInformation>
                                    <name>Observaciones</name>
                                    <value>{{$request['k_supplement']['observations']}}</value>
                                </AdditionalInformation>
                            </Collection>
                        </Group>
                    </Interoperabilidad>
                    <TotalesCop>
                        <FctConvCop>{{$request['k_supplement']['FctConvCop']}}</FctConvCop>
                        <MonedaCop>{{$request['k_supplement']['MonedaCop']}}</MonedaCop>
                        <SubTotalCop>{{$request['k_supplement']['SubTotalCop']}}</SubTotalCop>
                        <DescuentoDetalleCop>{{$request['k_supplement']['DescuentoDetalleCop']}}</DescuentoDetalleCop>
                        <RecargoDetalleCop>{{$request['k_supplement']['RecargoDetalleCop']}}</RecargoDetalleCop>
                        <TotalBrutoFacturaCop>{{$request['k_supplement']['TotalBrutoFacturaCop']}}</TotalBrutoFacturaCop>
                        <TotIvaCop>{{$request['k_supplement']['TotIvaCop']}}</TotIvaCop>
                        <TotIncCop>{{$request['k_supplement']['TotIncCop']}}</TotIncCop>
                        <TotBolCop>{{$request['k_supplement']['TotBolCop']}}</TotBolCop>
                        <ImpOtroCop>{{$request['k_supplement']['ImpOtroCop']}}</ImpOtroCop>
                        <MntImpCop>{{$request['k_supplement']['MntImpCop']}}</MntImpCop>
                        <TotalNetoFacturaCop>{{$request['k_supplement']['TotalNetoFacturaCop']}}</TotalNetoFacturaCop>
                        <MntDctoCop>{{$request['k_supplement']['MntDctoCop']}}</MntDctoCop>
                        <MntRcgoCop>{{$request['k_supplement']['MntRcgoCop']}}</MntRcgoCop>
                        <VlrPagarCop>{{$request['k_supplement']['VlrPagarCop']}}</VlrPagarCop>
                        <ReteFueCop>{{$request['k_supplement']['ReteFueCop']}}</ReteFueCop>
                        <ReteIvaCop>{{$request['k_supplement']['ReteIvaCop']}}</ReteIvaCop>
                        <ReteIcaCop>{{$request['k_supplement']['ReteIcaCop']}}</ReteIcaCop>
                        <TotAnticiposCop>{{$request['k_supplement']['TotAnticiposCop']}}</TotAnticiposCop>
                    </TotalesCop>
                </CustomTagGeneral>
            </ext:ExtensionContent>
        </ext:UBLExtension>
    @endif
</ext:UBLExtensions>
